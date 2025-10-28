# Story 1.8: Automatic Metadata Extraction with Admin Confirmation

**As an** administrator,
**I want** the system to automatically extract metadata from registered and uploaded files,
**so that** I don't have to manually enter titles, authors, and publication dates.

---

## Acceptance Criteria

1. ✅ Metadata extraction runs as background queue job after file registration (from external server paths) or upload (to internal storage)
2. ✅ Extracts: Title, Author(s), Publication Year, Publisher, ISBN/DOI (if available)
3. ✅ Extraction rules configurable per content type (Books use ISBN patterns, Articles use DOI patterns, etc.)
4. ✅ Extracted metadata presented to admin for review and confirmation
5. ✅ Admin can accept, edit, or reject extracted metadata
6. ✅ Extraction status tracked (Pending, Processed, Failed, Confirmed)
7. ✅ Manual metadata entry always available as fallback
8. ✅ Extraction errors logged with file context for debugging
9. ✅ **Extraction supports all document formats: PDF, EPUB, TXT, DOC, DOCX, FB2, DJVU** *(Updated from AC: "Extraction supports multiple document formats")*

---

## Integration Verification

- **IV1**: Metadata extraction jobs process without blocking user requests
  - Extraction must complete within 30 seconds per file
  - UI responsive while extraction happens in background queue

- **IV2**: Failed extraction doesn't prevent publication creation (falls back to manual entry)
  - Admin can reject extraction and manually enter all metadata
  - Publication created successfully with manual metadata

- **IV3**: Extracted metadata populates correct fields in publication form
  - All extracted fields (title, authors, year, publisher, ISBN, DOI) appear in form
  - Pre-filled with extracted values, editable before submission

- **IV4**: Extraction works identically for external server files (registered) and internal storage files (uploaded)
  - Both registration (Story 1.6) and upload paths trigger identical extraction process
  - Same metadata review workflow for both paths

---

## Supported Document Formats

| Format | Extension | MIME Type | Extractor Library | Metadata Sources |
|--------|-----------|-----------|-------------------|------------------|
| **PDF** | `.pdf` | `application/pdf` | `smalot/pdfparser` | Embedded metadata, text patterns (ISBN, DOI), headers/footers |
| **EPUB** | `.epub` | `application/epub+zip` | `PHPePub` or `ZipArchive` | OPF metadata file, manifest |
| **TXT** | `.txt` | `text/plain` | Native regex patterns | Filename, first lines, known delimiters |
| **DOC** | `.doc` | `application/msword` | `PHPOffice/PHPWord` (limited) | Document properties, text patterns |
| **DOCX** | `.docx` | `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | `PHPOffice/PHPWord` | core.xml properties, custom properties |
| **FB2** | `.fb2` | `text/xml` or `application/x-fictionbook` | Native XML parsing | Book element metadata (title, author, publisher) |
| **DJVU** | `.djvu` | `image/vnd.djvu` | Third-party DJVU library or OCR fallback | OCR text extraction, embedded metadata |

### Format-Specific Extraction Rules

**PDF Files:**
- Extract embedded title/author from PDF metadata dictionary
- Pattern matching for ISBN (10 or 13 digits) in document text
- Pattern matching for DOI (10.xxxx/xxxx format)
- Extract from bookmarks or document info dictionary
- Fallback: Use filename as title suggestion

**EPUB Files:**
- Parse package.opf file for standard Dublin Core metadata
- Extract: title, creator (author), date, publisher
- Handle multiple authors (creator elements)
- Language-aware extraction (supports multilingual titles)

**DOCX Files:**
- Extract from core.xml (docProps): title, creator, lastModifiedBy
- Extract from custom.xml if present
- Parse document body for title/author patterns (bold first lines, headers)

**DOC Files:**
- Limited support via PHPOffice (legacy format challenges)
- Extract document properties if available
- Fallback to filename and text pattern matching

**TXT Files:**
- Parse first 500 characters for author/title patterns
- Support known delimiters: "by " for authors, ":" for title
- Filename-based suggestions if no patterns found

**FB2 Files:**
- XML native parsing for book metadata
- Extract from `<book-title>`, `<author>`, `<publisher>`, `<date>` elements
- Handle multiple authors and genres
- Extract language attribute

**DJVU Files:**
- Attempt OCR text extraction for metadata patterns
- Fallback to embedded metadata if available
- Filename-based suggestions
- Note: Full text extraction may be computationally expensive

---

## Configuration Updates Required

### 1. Update `config/library.php`

```php
'upload' => [
    'max_file_size' => env('LIBRARY_MAX_UPLOAD_SIZE', 524288000), // 500MB
    'allowed_mime_types' => [
        'application/pdf',
        'application/epub+zip',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/xml',  // FB2
        'application/x-fictionbook',  // FB2
        'image/vnd.djvu',
    ],
    'allowed_extensions' => ['pdf', 'epub', 'txt', 'doc', 'docx', 'fb2', 'djvu'],
],

/*
|--------------------------------------------------------------------------
| Metadata Extraction Configuration
|--------------------------------------------------------------------------
|
| Configure extraction behavior, timeouts, and retry logic.
|
*/
'extraction' => [
    'enabled' => env('METADATA_EXTRACTION_ENABLED', true),
    'timeout_seconds' => env('METADATA_EXTRACTION_TIMEOUT', 30),
    'max_retries' => env('METADATA_EXTRACTION_RETRIES', 3),
    'djvu_enable_ocr' => env('DJVU_ENABLE_OCR', false),  // Expensive, disabled by default
    'confidence_threshold' => env('EXTRACTION_CONFIDENCE_THRESHOLD', 0.6),  // Only show extractions > 60% confident
],
```

---

## Tasks & Subtasks

### Task 1: Create MetadataExtractor Service Framework

**Objective**: Build extensible metadata extraction service supporting all 7 formats

- [ ] Create `app/Services/MetadataExtractors/MetadataExtractorInterface.php`
  - [ ] Define interface: `extract(string $filePath): ExtractedMetadata`
  - [ ] Return type: `ExtractedMetadata` DTO with fields and confidence scores

- [ ] Create `app/Services/MetadataExtractors/ExtractedMetadata.php` (Data Transfer Object)
  - [ ] Properties: `title`, `authors` (array), `publication_year`, `publisher`, `isbn`, `doi`, `confidence_score`
  - [ ] Methods: `getHighestConfidenceFields()`, `isEmpty()`
  - [ ] Each field includes: `value` and `confidence` (0.0-1.0)

- [ ] Create `app/Services/MetadataExtractors/AbstractMetadataExtractor.php` base class
  - [ ] Common logging and error handling
  - [ ] Helper methods for pattern matching (ISBN, DOI, date patterns)
  - [ ] Language detection helpers
  - [ ] Encoding normalization

- [ ] Create `app/Services/MetadataExtractors/MetadataExtractorFactory.php`
  - [ ] Factory method: `create(string $filePath, string $contentType): MetadataExtractorInterface`
  - [ ] Detect extractor based on MIME type
  - [ ] Fallback handling for unsupported formats

---

### Task 2: Implement Format-Specific Extractors (All 7 Formats)

**Objective**: Create specialized extractor for each document format

#### Sub-task 2.1: PDF Extractor
- [ ] Create `app/Services/MetadataExtractors/PDFMetadataExtractor.php`
- [ ] Use `smalot/pdfparser` library (add to composer.json if needed)
- [ ] Extract embedded metadata from PDF objects
- [ ] Pattern matching for ISBN (regex: `/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i`)
- [ ] DOI pattern matching (regex: `/10\.\d{4,}/`)
- [ ] Extract from document properties dictionary
- [ ] Handle multi-author PDFs
- [ ] Confidence scoring: High (0.9) for embedded metadata, Medium (0.6) for patterns

#### Sub-task 2.2: EPUB Extractor
- [ ] Create `app/Services/MetadataExtractors/EPUBMetadataExtractor.php`
- [ ] Use `ZipArchive` (native PHP) to extract OPF file
- [ ] Parse package.opf XML for Dublin Core metadata
- [ ] Extract: `dc:title`, `dc:creator`, `dc:publisher`, `dc:issued`
- [ ] Handle multiple authors (multiple `dc:creator` elements)
- [ ] Language-aware extraction from `xml:lang` attributes
- [ ] Confidence scoring: Very High (0.95) for OPF metadata

#### Sub-task 2.3: DOCX Extractor
- [ ] Create `app/Services/MetadataExtractors/DOCXMetadataExtractor.php`
- [ ] Use `PHPOffice/PHPWord` library
- [ ] Extract from `docProps/core.xml`: title, creator, lastModifiedBy
- [ ] Extract from `docProps/custom.xml` if exists
- [ ] Parse document body for title/author patterns (first bold text, H1 styles)
- [ ] Handle Word document properties tables
- [ ] Confidence scoring: High (0.85) for properties, Medium (0.5) for pattern matching

#### Sub-task 2.4: DOC Extractor
- [ ] Create `app/Services/MetadataExtractors/DOCMetadataExtractor.php`
- [ ] Use `PHPOffice/PHPWord` with legacy format support
- [ ] Extract document properties if accessible
- [ ] Fallback to text pattern matching for legacy DOC files
- [ ] Note: Partial extraction expected due to format limitations
- [ ] Confidence scoring: Medium (0.5) for legacy format reliability
- [ ] Log warnings for format compatibility

#### Sub-task 2.5: TXT Extractor
- [ ] Create `app/Services/MetadataExtractors/TXTMetadataExtractor.php`
- [ ] Parse first 500 characters for patterns
- [ ] Author pattern: "by [Author Name]" or "Author: [Author Name]"
- [ ] Title pattern: First line or colon-delimited first segment
- [ ] Support common delimiters: "by", "author", "title", ":"
- [ ] Extract filename as title suggestion if no patterns found
- [ ] Case-insensitive pattern matching
- [ ] Confidence scoring: Low-Medium (0.3-0.4) due to format ambiguity
- [ ] Handle encoding detection (UTF-8, ASCII, Latin-1)

#### Sub-task 2.6: FB2 Extractor
- [ ] Create `app/Services/MetadataExtractors/FB2MetadataExtractor.php`
- [ ] Native XML parsing (SimpleXML or DOMDocument)
- [ ] Extract from standard FB2 elements:
  - [ ] `<book-title>` → Title
  - [ ] `<author><first-name>`, `<middle-name>`, `<last-name>` → Authors
  - [ ] `<publisher>` → Publisher
  - [ ] `<date>` or `<year>` → Publication Year
  - [ ] `<genre>` → Genre metadata
- [ ] Handle multiple authors (multiple `<author>` elements)
- [ ] Extract language from root element
- [ ] Confidence scoring: Very High (0.95) for standard FB2 structure

#### Sub-task 2.7: DJVU Extractor
- [ ] Create `app/Services/MetadataExtractors/DJVUMetadataExtractor.php`
- [ ] Attempt native DJVU metadata extraction (if library available)
- [ ] Implement filename-based fallback extraction
- [ ] Optional: OCR text extraction for pattern matching (if `DJVU_ENABLE_OCR=true`)
  - [ ] Use `tesseract` or similar OCR library
  - [ ] Extract first page OCR for title/author patterns
  - [ ] Performance: Cache OCR results, warn if expensive
- [ ] Confidence scoring: Low (0.3) for filename, Very High (0.9) for OCR if available
- [ ] Log performance metrics for OCR operations

---

### Task 3: Create ExtractionRules Configuration System

**Objective**: Allow admins to customize extraction behavior per content type

- [ ] Create `app/Models/ExtractionRule.php` Eloquent model
  - [ ] Relationships: `belongsTo(ContentType)`
  - [ ] Properties: `content_type_id`, `format`, `priority`, `pattern_type`, `pattern`, `target_field`, `enabled`, `created_at`, `updated_at`
  - [ ] Format types: regex, delimiter, field_mapping, xpath (for XML)

- [ ] Database migration: `create_extraction_rules_table`
  - [ ] Columns: id, content_type_id (FK), format, priority, pattern_type, pattern, target_field, enabled, created_by (FK), updated_by (FK), created_at, updated_at
  - [ ] Indexes: (content_type_id, format), (content_type_id, priority)
  - [ ] Unique constraint: (content_type_id, format, target_field, priority)

- [ ] Create `app/Services/ExtractionRuleManager.php` service
  - [ ] Load rules for content type: `getRulesForContentType(int $contentTypeId): Collection`
  - [ ] Apply rules to extracted data: `applyRules(ExtractedMetadata $metadata, int $contentTypeId): ExtractedMetadata`
  - [ ] Validate rule syntax before saving
  - [ ] Cache rules by content type ID

- [ ] Seed default extraction rules (`database/seeders/ExtractionRulesSeeder.php`)
  - [ ] **Books** (PDF/EPUB/DOCX):
    - [ ] ISBN extraction regex (priority 1)
    - [ ] DOI extraction regex (priority 2)
    - [ ] Author pattern matching (priority 3)
  - [ ] **Articles** (PDF/TXT):
    - [ ] DOI extraction regex (priority 1)
    - [ ] ISSN extraction (priority 2)
  - [ ] **Magazines** (PDF/EPUB):
    - [ ] ISSN extraction (priority 1)
    - [ ] Issue date extraction (priority 2)
  - [ ] **Fiction** (EPUB/FB2):
    - [ ] Standard FB2 field mapping (priority 1)
  - [ ] **All types** (Generic):
    - [ ] Filename-based title suggestion (lowest priority)

- [ ] Create Livewire component: `app/Livewire/Admin/ExtractionRuleManager.php`
  - [ ] List rules by content type (table with filter dropdown)
  - [ ] Create/Edit rule form with live validation
  - [ ] Test rule against sample file (upload or select from library)
  - [ ] Rule test preview: Shows matched values, confidence scores
  - [ ] Drag-to-reorder rules by priority
  - [ ] Enable/disable toggle without deletion
  - [ ] Delete rule with confirmation

---

### Task 4: Create FileMetadata Model and Storage

**Objective**: Persist extraction results for admin review

- [ ] Create `app/Models/FileMetadata.php` Eloquent model
  - [ ] Relationships: `belongsTo(File)`, `morphToMany(Publication)` (for linking to publication)
  - [ ] Properties:
    - `file_id` (FK)
    - `status` (enum: pending, processed, failed, confirmed, rejected)
    - `extracted_data` (JSON: `{title, authors[], publication_year, publisher, isbn, doi}`)
    - `extraction_method` (which extractor was used: pdf_extractor, epub_extractor, etc.)
    - `confidence_scores` (JSON: `{title_confidence, author_confidence, ...}`)
    - `error_message` (nullable, for failed extractions)
    - `extracted_at` (timestamp)
    - `confirmed_at` (timestamp, when admin confirmed)
    - `rejected_at` (timestamp, when admin rejected)
  - [ ] Accessor methods: `getTitle()`, `getAuthors()`, `getPublicationYear()`, `getPublisher()`, `getIsbn()`, `getDoi()`
  - [ ] Method: `getHighestConfidenceFields()` → returns fields with confidence > threshold
  - [ ] Method: `reject()` → sets status to rejected
  - [ ] Method: `confirm()` → sets status to confirmed, stores confirmation timestamp

- [ ] Database migration: `create_file_metadatas_table`
  - [ ] Columns:
    ```sql
    id, file_id (FK), status (enum), extracted_data (json),
    extraction_method (string), confidence_scores (json),
    error_message (text, nullable), extracted_at (timestamp, nullable),
    confirmed_at (timestamp, nullable), rejected_at (timestamp, nullable),
    created_at, updated_at
    ```
  - [ ] Indexes: (file_id), (status), (created_at)
  - [ ] Foreign key constraint: file_id → files.id (onDelete: cascade)

---

### Task 5: Create MetadataExtraction Queue Job

**Objective**: Process metadata extraction asynchronously without blocking requests

- [ ] Create `app/Jobs/ExtractMetadataFromFile.php` job
  - [ ] Constructor parameters: `string $fileId, string $filePath, string $contentTypeId`
  - [ ] Implement `ShouldQueue` interface with timeout (30 seconds)
  - [ ] Job logic:
    ```
    1. Load File model and validate path exists
    2. Detect MIME type and file format
    3. Load MetadataExtractorFactory
    4. Create appropriate extractor (PDF, EPUB, etc.)
    5. Call extractor->extract($filePath)
    6. Create FileMetadata record with extracted_data and confidence_scores
    7. Set status to "processed" or "failed"
    8. Log all steps
    9. Fire event: MetadataExtracted($fileMetadata)
    ```
  - [ ] Error handling:
    - [ ] Catch file access errors → log and set status failed with error_message
    - [ ] Catch extraction errors → log and set status failed
    - [ ] Retry policy: 3 attempts, exponential backoff
  - [ ] Logging: Use Laravel's Log facade with context (file_id, file_path, extractor, duration)

- [ ] Create `app/Events/MetadataExtracted.php` event
  - [ ] Properties: `fileMetadata`
  - [ ] Should broadcast or dispatch listeners

- [ ] Create `app/Listeners/NotifyAdminOfMetadataReady.php` listener
  - [ ] Send notification when extraction completes (Livewire event or email)

---

### Task 6: Create MetadataReviewForm Component

**Objective**: Allow admin to review, edit, and confirm extracted metadata

- [ ] Create `app/Livewire/Admin/MetadataReviewForm.php` Livewire component
  - [ ] Display file info: filename, format, file size, upload/registration date
  - [ ] Display extraction status: "Processing", "Ready for Review", "Confirmed", "Failed"
  - [ ] Show confidence scores: "Title (92% confident)", "Author (78% confident)"

  - [ ] Editable form fields:
    - [ ] Title (text input, pre-filled with extracted value)
    - [ ] Authors (repeating field, pre-filled with extracted authors)
    - [ ] Publication Year (date picker, pre-filled)
    - [ ] Publisher (text input, pre-filled)
    - [ ] ISBN (text input, pre-filled if available)
    - [ ] DOI (text input, pre-filled if available)

  - [ ] Admin actions:
    - [ ] Button: "Confirm Extraction" → saves form and marks FileMetadata as confirmed
    - [ ] Button: "Edit & Confirm" → allows editing before confirmation
    - [ ] Button: "Reject Extraction" → marks as rejected, shows manual entry form
    - [ ] Link: "See Extraction Details" → shows which extractor used, confidence scores, raw extracted data

  - [ ] Manual entry fallback section:
    - [ ] Always visible alongside extracted data
    - [ ] Empty form for admin to manually fill all fields
    - [ ] Button: "Save Manual Entry" → creates FileMetadata with manual_entry=true status

  - [ ] Conditional rendering:
    - [ ] If status = pending: Show "Extraction in progress..." message
    - [ ] If status = processed: Show extracted values for review
    - [ ] If status = failed: Show error message and manual entry form
    - [ ] If status = confirmed: Show confirmation date and edit option

  - [ ] Real-time validation:
    - [ ] Authors field: validate as non-empty array
    - [ ] ISBN/DOI: validate format if provided
    - [ ] Publication year: validate as valid year
    - [ ] Title: validate as non-empty

---

### Task 7: Create MetadataReviewQueue Dashboard

**Objective**: Provide admin interface to manage pending metadata reviews

- [ ] Create `app/Livewire/Admin/MetadataReviewQueue.php` Livewire component
  - [ ] List view of files awaiting metadata review:
    - [ ] Columns: Filename, Format, Status Badge, Extraction Date, Actions
    - [ ] Pagination: 20 items per page

  - [ ] Status badges:
    - [ ] "⏳ Pending Extraction" (status = pending)
    - [ ] "📋 Ready for Review" (status = processed)
    - [ ] "✅ Confirmed" (status = confirmed)
    - [ ] "❌ Failed" (status = failed)
    - [ ] "🚫 Rejected" (status = rejected)

  - [ ] Quick preview on hover: Shows extracted title/authors snippet

  - [ ] Filter options (Livewire reactive):
    - [ ] By Status: Pending, Ready, Confirmed, Failed, All
    - [ ] By Format: PDF, EPUB, DOCX, TXT, FB2, DJVU, All
    - [ ] By Date: Last 24h, Last 7 days, Last 30 days, All
    - [ ] By Content Type: Dropdown of all content types

  - [ ] Sort options:
    - [ ] By Extraction Date (newest first, default)
    - [ ] By Filename (A-Z)
    - [ ] By Status

  - [ ] Bulk actions toolbar (checkboxes for multi-select):
    - [ ] Button: "Confirm All Selected" → batch update status to confirmed
    - [ ] Button: "Reject All Selected" → batch update status to rejected
    - [ ] Button: "Re-extract Selected" → dispatch ExtractMetadataFromFile job again
    - [ ] Batch action count: "5 items selected"

  - [ ] Row actions:
    - [ ] Button: "Review & Confirm" → Opens MetadataReviewForm modal/drawer
    - [ ] Button: "Retry Extraction" → Dispatch job again for failed extractions
    - [ ] Button: "Delete" → Remove FileMetadata record with confirmation

  - [ ] Statistics summary at top:
    - [ ] "📊 Total pending: 12 | Processing: 3 | Confirmed: 487 | Failed: 2"

---

### Task 8: Integrate with File Registration (Story 1.6)

**Objective**: Automatically trigger metadata extraction after file registration

- [ ] Modify `app/Http/Controllers/Admin/FileRegistrationController.php`
  - [ ] In `registerFile()` method, after creating File record:
    - [ ] Dispatch `ExtractMetadataFromFile::dispatch($file->id, $file->path, $contentTypeId)`
    - [ ] Flash message to user: "File registered. Metadata extraction started..."

- [ ] Update view: `resources/views/livewire/admin/file-registration-form.blade.php`
  - [ ] Update line 43: Change "Allowed formats: PDF, EPUB, TXT, DOCX"
  - [ ] To: "Allowed formats: PDF, EPUB, TXT, DOC, DOCX, FB2, DJVU"
  - [ ] Add note: "Metadata will be automatically extracted after registration"

- [ ] Add success message modal/alert after registration:
  - [ ] Show file info and extraction status
  - [ ] Link: "Review extracted metadata" → navigate to MetadataReviewQueue
  - [ ] Button: "Register another file" → reset form

---

### Task 9: Integrate with File Upload (Story 1.6)

**Objective**: Automatically trigger metadata extraction after file upload

- [ ] Modify `app/Http/Controllers/Admin/FileUploadController.php` (or equivalent Livewire component)
  - [ ] After successful file upload and storage:
    - [ ] Create File record in database
    - [ ] Dispatch `ExtractMetadataFromFile::dispatch($file->id, $storagePath, $contentTypeId)`
    - [ ] Flash message: "File uploaded. Metadata extraction started..."

- [ ] UI feedback:
  - [ ] Show progress indicator: "Processing file... (Extracting metadata)"
  - [ ] After extraction completes: "Extraction complete. Review & confirm metadata"
  - [ ] Link to MetadataReviewForm

- [ ] Handle upload + extraction flow:
  - [ ] User uploads file
  - [ ] File stored to `storage/app/content/{type}/`
  - [ ] File record created
  - [ ] Extraction job dispatched
  - [ ] User sees status message
  - [ ] Redirect to metadata review or dashboard

---

### Task 10: Update Configuration Files

**Objective**: Add extraction config and update file format support

- [ ] Update `config/library.php`:
  - [ ] Add all 7 file extensions to `allowed_extensions`
  - [ ] Add all MIME types to `allowed_mime_types`
  - [ ] Add new `extraction` section with config keys
  - [ ] See "Configuration Updates Required" section above

- [ ] Update `.env.example`:
  - [ ] Add keys: `LIBRARY_MAX_UPLOAD_SIZE`, `METADATA_EXTRACTION_ENABLED`, `METADATA_EXTRACTION_TIMEOUT`, `METADATA_EXTRACTION_RETRIES`, `DJVU_ENABLE_OCR`, `EXTRACTION_CONFIDENCE_THRESHOLD`

- [ ] Update composer.json dependencies:
  - [ ] `smalot/pdfparser` for PDF extraction
  - [ ] Verify `PHPOffice/PHPWord` is included for DOCX/DOC
  - [ ] Add any additional libraries needed for FB2/DJVU extraction
  - [ ] Example: `php-djvu/php-djvu` or alternative DJVU library

---

### Task 11: Write Comprehensive Tests

**Objective**: Ensure extraction works reliably across all formats

#### Test 1: Unit Tests for Format-Specific Extractors

- [ ] Create `tests/Unit/Services/MetadataExtractors/PDFMetadataExtractorTest.php`
  - [ ] Test extraction from sample PDF with title/author metadata
  - [ ] Test ISBN pattern matching
  - [ ] Test DOI pattern matching
  - [ ] Test graceful handling of missing metadata
  - [ ] Test confidence score calculation

- [ ] Create `tests/Unit/Services/MetadataExtractors/EPUBMetadataExtractorTest.php`
  - [ ] Test OPF file parsing
  - [ ] Test multiple authors extraction
  - [ ] Test multilingual title support
  - [ ] Test missing metadata handling

- [ ] Create `tests/Unit/Services/MetadataExtractors/DOCXMetadataExtractorTest.php`
  - [ ] Test core.xml property extraction
  - [ ] Test custom.xml parsing
  - [ ] Test title pattern matching from document body

- [ ] Create `tests/Unit/Services/MetadataExtractors/TXTMetadataExtractorTest.php`
  - [ ] Test author pattern matching ("by Author")
  - [ ] Test title extraction from first lines
  - [ ] Test filename fallback
  - [ ] Test encoding detection

- [ ] Create `tests/Unit/Services/MetadataExtractors/FB2MetadataExtractorTest.php`
  - [ ] Test standard FB2 element extraction
  - [ ] Test multiple authors
  - [ ] Test language attribute parsing

#### Test 2: Unit Tests for Queue Job

- [ ] Create `tests/Unit/Jobs/ExtractMetadataFromFileTest.php`
  - [ ] Test job dispatches correctly with correct parameters
  - [ ] Test job processes file without errors
  - [ ] Test FileMetadata record created with extracted_data
  - [ ] Test status set to "processed" on success
  - [ ] Test status set to "failed" on error
  - [ ] Test error_message populated on failure
  - [ ] Test retry logic (max 3 attempts)
  - [ ] Test logging with context
  - [ ] Mock MetadataExtractor to isolate job logic

#### Test 3: Feature Tests for Admin UI

- [ ] Create `tests/Feature/Admin/MetadataReviewFormTest.php`
  - [ ] Test form renders with extracted metadata pre-filled
  - [ ] Test admin can confirm extracted metadata
  - [ ] Test admin can edit extracted values
  - [ ] Test admin can reject extraction and use manual entry
  - [ ] Test form validation (title required, authors array, etc.)
  - [ ] Test submission creates FileMetadata with confirmed status
  - [ ] Test manual entry submission works independently

- [ ] Create `tests/Feature/Admin/MetadataReviewQueueTest.php`
  - [ ] Test dashboard lists pending metadata items
  - [ ] Test status filters work correctly
  - [ ] Test format filters work correctly
  - [ ] Test date range filters work correctly
  - [ ] Test sorting by extraction date, filename, status
  - [ ] Test bulk confirm action
  - [ ] Test bulk reject action
  - [ ] Test individual row actions
  - [ ] Test pagination

#### Test 4: Integration Tests (End-to-End)

- [ ] Create `tests/Integration/FileRegistrationWithMetadataExtractionTest.php`
  - [ ] Test complete flow: Register file → Extract metadata → Review → Confirm → Publication created
  - [ ] Use real test file (PDF with metadata)
  - [ ] Process queue jobs synchronously for testing
  - [ ] Verify FileMetadata record created
  - [ ] Verify publication record populated with confirmed metadata

- [ ] Create `tests/Integration/FileUploadWithMetadataExtractionTest.php`
  - [ ] Test complete flow: Upload file → Extract metadata → Review → Confirm → Publication created
  - [ ] Test with multiple file formats (PDF, EPUB, DOCX)
  - [ ] Verify file stored correctly
  - [ ] Verify extraction completes
  - [ ] Verify metadata review form accessible

- [ ] Create `tests/Integration/MetadataExtractionForAllFormatsTest.php`
  - [ ] Test extraction for each format: PDF, EPUB, TXT, DOC, DOCX, FB2, DJVU
  - [ ] Use real test files with various metadata
  - [ ] Verify correct extractor used for each format
  - [ ] Verify confidence scores assigned appropriately
  - [ ] Verify fallback handling for missing metadata

#### Test 5: Performance Tests

- [ ] Create `tests/Feature/MetadataExtractionPerformanceTest.php`
  - [ ] Test extraction from large PDF (>50MB) completes within 30 seconds
  - [ ] Test extraction doesn't block page load (async queue job)
  - [ ] Test bulk metadata review with 100 items loads in <2 seconds
  - [ ] Test filtering 1000 items by status is responsive (<500ms)

#### Test 6: Regression Tests

- [ ] All existing Story 1.6 (file registration) tests still pass
- [ ] All existing Story 1.7 (bulk folder scan) tests still pass
- [ ] Verify no N+1 queries in dashboard listing
- [ ] Verify no queue deadlocks with multiple concurrent extractions

---

### Task 12: Create Documentation

**Objective**: Document metadata extraction system for developers and admins

- [ ] Create `docs/metadata-extraction.md`:
  - [ ] Overview of metadata extraction system
  - [ ] Supported file formats and extraction methods
  - [ ] Confidence scoring explanation
  - [ ] Configuration guide (`.env` keys, `config/library.php`)
  - [ ] How to customize extraction rules
  - [ ] How to add support for new file formats
  - [ ] Troubleshooting failed extractions
  - [ ] Performance tuning (timeouts, OCR, caching)

- [ ] Add code comments:
  - [ ] Document each MetadataExtractor class
  - [ ] Explain confidence scoring algorithm
  - [ ] Document extraction rule pattern syntax
  - [ ] Explain error handling and retry logic

- [ ] Create admin guide:
  - [ ] Screenshots of extraction rule manager
  - [ ] Screenshots of metadata review dashboard
  - [ ] Step-by-step: How to configure extraction rules
  - [ ] Step-by-step: How to review and confirm metadata
  - [ ] Troubleshooting: What to do if extraction fails

---

## Acceptance Criteria → Task Mapping

| AC # | Task(s) | Verification |
|------|---------|--------------|
| 1 | Tasks 5, 8, 9 | File registration/upload dispatches ExtractMetadataFromFile job |
| 2 | Tasks 1, 2 | All extractors return title, authors, year, publisher, ISBN, DOI |
| 3 | Task 3 | ExtractionRuleManager applies rules per content type |
| 4 | Task 6 | MetadataReviewForm displays extracted metadata |
| 5 | Task 6 | Admin can edit and confirm extracted values |
| 6 | Task 4 | FileMetadata status tracked (pending→processed→confirmed) |
| 7 | Task 6 | Manual entry form always available as fallback |
| 8 | Task 5 | Extraction job logs errors with file context |
| 9 | Tasks 1, 2 | All 7 formats (PDF, EPUB, TXT, DOC, DOCX, FB2, DJVU) supported |

---

## Integration Verification → Task Mapping

| IV # | Implementation | How to Verify |
|------|---|---|
| IV1 | Task 5 (async job) | Run `php artisan queue:work`; extract from file; verify page responsive |
| IV2 | Task 6 (fallback form) | Reject extraction; fill form manually; verify publication created |
| IV3 | Task 6 (form pre-fill) | Extract from file; verify all fields populated in form; edit values |
| IV4 | Tasks 8, 9 (both paths) | Register file AND upload file; verify both trigger identical extraction workflow |

---

## Story Dependencies

- **Depends on**: Story 1.6 (File Registration), Story 1.7 (Bulk Folder Scanning)
- **Prerequisites**: File models, storage system, queue configuration
- **Blocks**: Story 1.10+ (Publication details page) which may use extracted metadata

---

## Story Status Summary

**Completeness**: ✅ **READY FOR REVIEW**

✅ User story with clear persona and goal
✅ 9 detailed acceptance criteria (including all 7 file formats)
✅ 4 comprehensive integration verification scenarios
✅ 12 detailed implementation tasks with specific subtasks
✅ Format-specific extraction strategies documented
✅ Configuration changes specified
✅ Comprehensive test coverage specification
✅ Documentation requirements identified
✅ Dependency clarity (depends on Stories 1.6, 1.7)
✅ Estimation-ready (can be broken into design/impl/test sprints)

---

## Effort Estimate (Story Points / Hours)

- **Complexity**: Medium-High (multiple formats, async processing, admin UI)
- **Estimated SP**: 13-21 points (3-4 week sprint)
- **Breakdown**:
  - Framework & interfaces: 2 SP
  - Format extractors (all 7): 5 SP (1 SP per format, some overlap)
  - Rules system: 3 SP
  - Queue job: 2 SP
  - Admin UI (review form + dashboard): 5 SP
  - Tests: 5 SP
  - Documentation: 2 SP

---

**Story prepared by**: Product Owner Sarah
**Date**: 2025-10-26
**Format version**: 1.8-comprehensive-with-all-formats
