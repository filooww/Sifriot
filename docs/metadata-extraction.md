# Metadata Extraction System Documentation

## Overview

The Metadata Extraction System automatically extracts publication metadata (title, authors, publication year, publisher, ISBN, DOI) from uploaded or registered files. This system processes files asynchronously via Laravel's queue system, allowing admins to review and confirm extracted data before publication creation.

## Supported File Formats

| Format | Extension | MIME Type | Extractor | Confidence | Notes |
|--------|-----------|-----------|-----------|------------|-------|
| **PDF** | `.pdf` | `application/pdf` | `PDFMetadataExtractor` | High (0.6-0.9) | Uses embedded metadata, pattern matching for ISBN/DOI |
| **EPUB** | `.epub` | `application/epub+zip` | `EPUBMetadataExtractor` | Very High (0.95) | Parses package.opf (Dublin Core metadata) |
| **DOCX** | `.docx` | `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | `DOCXMetadataExtractor` | High (0.5-0.85) | Extracts from docProps/core.xml and document body |
| **DOC** | `.doc` | `application/msword` | `DOCMetadataExtractor` | Medium (0.5) | Limited support, fallback to filename patterns |
| **TXT** | `.txt` | `text/plain` | `TXTMetadataExtractor` | Low-Medium (0.2-0.4) | Pattern matching from first 500 characters |
| **FB2** | `.fb2` | `application/x-fictionbook` | `FB2MetadataExtractor` | Very High (0.95) | Native XML parsing for FictionBook format |
| **DJVU** | `.djvu` | `image/vnd.djvu` | `DJVUMetadataExtractor` | Low-Medium (0.2-0.3) | Filename fallback, optional OCR (expensive) |

## Architecture

### Core Components

#### 1. **MetadataExtractorInterface** (`app/Services/MetadataExtractors/MetadataExtractorInterface.php`)
Contract defining the extraction behavior. All extractors implement:
```php
public function extract(string $filePath): ExtractedMetadata;
```

#### 2. **ExtractedMetadata DTO** (`app/Services/MetadataExtractors/ExtractedMetadata.php`)
Data Transfer Object carrying extracted metadata with confidence scores:
```php
$metadata
    ->setTitle('Book Title', 0.95)
    ->addAuthor('Author Name', 0.9)
    ->setPublicationYear(2023, 0.8)
    ->setPublisher('Publisher', 0.7)
    ->setIsbn('978-3-16-148410-0', 0.95)
    ->setDoi('10.1234/example', 0.85);
```

#### 3. **AbstractMetadataExtractor** (`app/Services/MetadataExtractors/AbstractMetadataExtractor.php`)
Base class providing common utilities:
- Pattern matching for ISBN/DOI
- Year extraction
- Text encoding normalization
- Language detection
- Logging integration

#### 4. **MetadataExtractorFactory** (`app/Services/MetadataExtractors/MetadataExtractorFactory.php`)
Factory class for creating appropriate extractors:
```php
$extractor = MetadataExtractorFactory::create('/path/to/file.pdf', 'application/pdf');
$metadata = $extractor->extract('/path/to/file.pdf');
```

#### 5. **Format-Specific Extractors** (`app/Services/MetadataExtractors/Extractors/`)
Specialized extractors for each format:
- `PDFMetadataExtractor` - Uses smalot/pdfparser library
- `EPUBMetadataExtractor` - Parses OPF XML structure
- `DOCXMetadataExtractor` - Extracts from ZIP-compressed XML
- `DOCMetadataExtractor` - Limited support via PHPOffice
- `TXTMetadataExtractor` - Pattern matching from text
- `FB2MetadataExtractor` - Native XML parsing
- `DJVUMetadataExtractor` - Filename fallback + optional OCR

### Job & Queue Integration

#### **ExtractMetadataFromFile Job** (`app/Jobs/ExtractMetadataFromFile.php`)
Asynchronous queue job that:
1. Validates file existence
2. Detects MIME type
3. Creates/updates FileMetadata record (status: pending)
4. Instantiates appropriate extractor
5. Extracts metadata
6. Saves results to database
7. Fires `MetadataExtracted` event
8. Logs operations with context

**Configuration:**
- **Timeout:** 30 seconds (configurable via `METADATA_EXTRACTION_TIMEOUT`)
- **Retries:** 3 attempts with exponential backoff
- **Queue:** Default queue (respects `QUEUE_CONNECTION` setting)

#### **MetadataExtracted Event** (`app/Events/MetadataExtracted.php`)
Fired when extraction completes successfully.

#### **NotifyAdminOfMetadataReady Listener** (`app/Listeners/NotifyAdminOfMetadataReady.php`)
Listens to extraction completion and logs notification (can be extended for email/notifications).

### Database Models

#### **FileMetadata Model** (`app/Models/FileMetadata.php`)
Stores extraction results:
```php
$metadata = FileMetadata::find(1);
$metadata->getTitle();        // Get extracted title
$metadata->getAuthors();      // Get authors array
$metadata->getPublicationYear(); // Get year
$metadata->getPublisher();    // Get publisher
$metadata->getIsbn();         // Get ISBN
$metadata->getDoi();          // Get DOI
```

**Statuses:**
- `pending` - Extraction in progress
- `processed` - Ready for admin review
- `confirmed` - Admin confirmed extraction
- `failed` - Extraction error
- `rejected` - Admin rejected extraction

**Methods:**
```php
$metadata->confirm();        // Mark as confirmed
$metadata->reject();         // Mark as rejected
$metadata->getHighestConfidenceFields(0.6); // Fields above threshold
```

#### **ExtractionRule Model** (`app/Models/ExtractionRule.php`)
Configuration rules for pattern-based extraction per content type:
```php
ExtractionRule::create([
    'content_type_id' => 1,
    'format' => 'pdf',
    'priority' => 1,
    'pattern_type' => 'regex',
    'pattern' => '/ISBN pattern/',
    'target_field' => 'isbn',
    'enabled' => true,
]);
```

### Admin UI Components

#### **MetadataReviewForm** (`app/Livewire/Admin/MetadataReviewForm.php`)
Livewire component for reviewing and confirming extracted metadata (Story 1.21 enhancement):
- **Features:**
  - Displays extraction status and confidence scores
  - Pre-filled editable form fields
  - Manual entry fallback
  - Confirm/reject/edit actions
  - Real-time validation
  - **NEW (1.21):** Genre dynamic list (add/remove multiple genres)
  - **NEW (1.21):** Theme/Category text field
  - **NEW (1.21):** Content Type dropdown (loaded from ContentType model)
  - **NEW (1.21):** Cover image file upload with preview (JPG/PNG/WebP, max 5MB)

- **Save Logic (Story 1.21):**
  - Authors → `authors` table + `author_publication` pivot (normalized)
  - Publisher → `publishings` table via `id_publishing` FK (normalized)
  - Genres → `genres` table + `genre_publication` pivot (NEW, normalized)
  - Cover Image → `files` table with `file_type='cover'` (NEW, normalized)
  - All saves wrapped in database transaction for consistency
  - Metadata stored in FileMetadata.extracted_data for audit trail and confidence tracking

#### **MetadataReviewQueue** (`app/Livewire/Admin/MetadataReviewQueue.php`)
Dashboard for managing pending metadata reviews:
- **Features:**
  - Statistics: pending, processing, confirmed, failed, rejected
  - Filtering: by status, format, date range, content type
  - Sorting: by date, filename, status
  - Bulk actions: confirm all, reject all, re-extract
  - Pagination (20 items per page)
  - Row actions: review, retry, delete

## Configuration

### Environment Variables
```bash
# Metadata Extraction Settings
METADATA_EXTRACTION_ENABLED=true
METADATA_EXTRACTION_TIMEOUT=30
METADATA_EXTRACTION_RETRIES=3
EXTRACTION_CONFIDENCE_THRESHOLD=0.6
DJVU_ENABLE_OCR=false
```

### config/library.php
```php
'extraction' => [
    'enabled' => env('METADATA_EXTRACTION_ENABLED', true),
    'timeout_seconds' => env('METADATA_EXTRACTION_TIMEOUT', 30),
    'max_retries' => env('METADATA_EXTRACTION_RETRIES', 3),
    'djvu_enable_ocr' => env('DJVU_ENABLE_OCR', false),
    'confidence_threshold' => env('EXTRACTION_CONFIDENCE_THRESHOLD', 0.6),
],

'upload' => [
    'allowed_extensions' => ['pdf', 'epub', 'txt', 'doc', 'docx', 'fb2', 'djvu'],
    'allowed_mime_types' => [
        'application/pdf',
        'application/epub+zip',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/x-fictionbook',
        'text/xml',
        'image/vnd.djvu',
    ],
],
```

## Usage Workflow

### 1. File Registration/Upload
When admin registers or uploads a file:
```php
// In FileRegistrationForm.php
ExtractMetadataFromFile::dispatch(
    $fileId,
    $filePath,
    $contentTypeId,
    $mimeType
);
```

### 2. Background Processing
Queue worker processes the job:
```bash
php artisan queue:work
```

### 3. Admin Review
Admin visits metadata review dashboard:
```
/admin/metadata-review-queue
```

### 4. Confirmation
Admin reviews extraction and confirms/edits metadata:
- Accepts extraction → status: confirmed
- Rejects and edits → status: confirmed (manual)
- Rejects without saving → status: rejected

## Confidence Scoring

Each extracted field includes a confidence score (0.0-1.0):

| Confidence | Color | Meaning | Action |
|------------|-------|---------|--------|
| 0.9-1.0 | Green | Very Confident | Accept as-is |
| 0.7-0.89 | Blue | Confident | Review, likely correct |
| 0.5-0.69 | Yellow | Moderate | Review carefully |
| <0.5 | Red | Low | Verify or edit |

**Default Confidence Threshold:** 0.6 (only show fields ≥60% confidence)

## API Examples

### Extract Metadata Programmatically
```php
use App\Services\MetadataExtractors\MetadataExtractorFactory;

$extractor = MetadataExtractorFactory::create('/path/to/file.pdf', 'pdf');
$metadata = $extractor->extract('/path/to/file.pdf');

echo $metadata->getTitle();      // "Book Title"
echo $metadata->getAuthors()[0]; // "Author Name"
echo $metadata->getIsbn();       // "978-3-16-148410-0"

// Get confidence scores
$scores = $metadata->getConfidenceScores();
echo $scores['title'];  // 0.95
```

### Dispatch Extraction Job
```php
use App\Jobs\ExtractMetadataFromFile;

ExtractMetadataFromFile::dispatch(
    'file-id-123',
    '/storage/app/content/books/file.pdf',
    1, // content_type_id
    'application/pdf'
);

// Or queue to specific queue/delay
ExtractMetadataFromFile::dispatch($fileId, $filePath, $contentTypeId)
    ->onQueue('extractions')
    ->delay(now()->addSeconds(10));
```

### Query FileMetadata
```php
use App\Models\FileMetadata;

// Get pending extractions
$pending = FileMetadata::pending()->get();

// Get confirmed metadata
$confirmed = FileMetadata::confirmed()->get();

// Get by content type and status
$readyForReview = FileMetadata::byStatus('processed')->get();

// Filter by extraction method
$pdfExtracted = FileMetadata::byMethod('PDFMetadataExtractor')->get();

// Get high-confidence fields
$metadata = FileMetadata::find(1);
$highConfidence = $metadata->getHighestConfidenceFields(0.8);
```

## Customization

### Adding a New File Format

1. **Create Extractor:**
```php
// app/Services/MetadataExtractors/Extractors/CustomExtractor.php
class CustomExtractor extends AbstractMetadataExtractor
{
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();
        // Extract logic here
        return $metadata;
    }
}
```

2. **Register in Factory:**
```php
// MetadataExtractorFactory::create()
'application/custom' => new CustomExtractor(),
```

3. **Update Configuration:**
```php
// config/library.php
'allowed_extensions' => [..., 'custom'],
'allowed_mime_types' => [..., 'application/custom'],
```

### Customizing Extraction Rules

Create extraction rules via admin UI or seed:
```php
ExtractionRule::create([
    'content_type_id' => 1,
    'format' => 'pdf',
    'priority' => 1,
    'pattern_type' => 'regex',
    'pattern' => '/your-custom-pattern/',
    'target_field' => 'isbn',
    'enabled' => true,
]);
```

## Troubleshooting

### Extraction Timeout
If extraction times out (>30s):
1. Check file size (>50MB may timeout)
2. Increase `METADATA_EXTRACTION_TIMEOUT` in `.env`
3. Check server resources (CPU, RAM)

### Missing Dependencies
Ensure required libraries are installed:
```bash
composer require smalot/pdfparser
composer require phpoffice/phpword
```

### Failed Extractions
Check logs for details:
```bash
tail -f storage/logs/folder_scan.log
```

**Common Issues:**
- File permissions (ensure readable)
- Corrupted file format
- Unsupported file format
- Library not installed

### Re-extraction
Retry failed extractions from dashboard or CLI:
```php
$metadata = FileMetadata::failed()->first();
ExtractMetadataFromFile::dispatch($metadata->file_id, $metadata->file_name, 1);
```

## Performance Notes

- **Extraction Time:** 1-10 seconds per file (depends on format/size)
- **Memory Usage:** 10-50MB per extraction (peaks during PDF parsing)
- **Database:** ~2KB per FileMetadata record
- **Caching:** Extraction rules cached for 1 hour

## Testing

Run tests:
```bash
php artisan test tests/Unit/Services/MetadataExtractors/
php artisan test tests/Feature/Admin/MetadataReviewForm
php artisan test tests/Unit/Jobs/ExtractMetadataFromFile
```

## Future Enhancements

- [ ] OCR support for DJVU (Tesseract integration)
- [ ] Machine learning confidence scoring
- [ ] Batch re-extraction for failed items
- [ ] Email notifications on extraction completion
- [ ] Webhook integration for external systems
- [ ] Support for additional formats (MOBI, AZW)

## Related Stories

- **Story 1.6:** File Registration/Upload (triggers extraction)
- **Story 1.7:** Bulk Folder Scanning (batch extraction)
- **Story 1.10+:** Publication details (uses confirmed metadata)
