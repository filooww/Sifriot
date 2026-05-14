# Universal Cover Extraction System

## Overview

The Universal Cover Extraction System provides automated cover image generation and extraction for multiple file formats in your application. It seamlessly integrates with the existing PDF cover extraction workflow while adding support for EPUB, DJVU, FB2, DOC, DOCX, and TXT formats.

## Architecture

The system consists of three main services:

### 1. `PdfCoverExtractorService` (Existing)
Handles PDF cover extraction using Imagick or ImageMagick. This service remains unchanged and maintains backward compatibility.

### 2. `DynamicCoverGeneratorService` (New)
Creates visually appealing placeholder covers for formats that don't contain embedded cover images.

**Features:**
- Gradient backgrounds with genre-specific color schemes
- Automatic text wrapping and font sizing
- Professional typography with shadows
- Format badges (EPUB, DOC, TXT, etc.)
- Responsive design for various title lengths

### 3. `UniversalCoverExtractorService` (New)
Unified service that routes files to appropriate extraction methods based on format.

**Supported Formats:**
- **PDF** → Routes to `PdfCoverExtractorService`
- **EPUB** → Extracts embedded cover from ZIP archive
- **DJVU** → Extracts first page using ddjvu or ImageMagick
- **FB2** → Parses XML to extract embedded images
- **DOC/DOCX/TXT** → Generates dynamic placeholder covers

## Format-Specific Handling

### PDF Cover Extraction
```php
// Routes to existing service - NO CHANGES
case 'pdf' => $this->pdfExtractor->extractFirstPage($filePath, $outputPath)
```

**Requirements:**
- Imagick PHP extension OR
- ImageMagick command-line tool

**Process:**
1. Opens PDF file
2. Extracts first page at 150 DPI
3. Converts to PNG format
4. Optimizes with 90% quality
5. Saves to storage

### EPUB Cover Extraction
```php
// Extracts embedded cover from ZIP archive
$zip = new ZipArchive();
$zip->open($epubPath);
// Searches for cover.jpg, cover.png, etc.
```

**Cover Locations Searched:**
- `OEBPS/cover.jpg` or `OEBPS/cover.png`
- `OEBPS/Images/cover.jpg` or `OEBPS/Images/cover.png`
- `cover.jpg` or `cover.png` (root level)
- `Images/cover.jpg` or `Images/cover.png`

**Fallback:** If no cover found, generates dynamic placeholder

### DJVU Cover Extraction
```php
// Similar to PDF extraction
$command = 'ddjvu -format=png -page=1 -quality=90 input.djvu output.png';
// Fallback to ImageMagick if ddjvu not available
```

**Requirements:**
- `ddjvu` command-line tool (from djvulibre package) OR
- ImageMagick with DJVU support

### FB2 Cover Extraction
```php
// Parses XML to find embedded images
$xml = simplexml_load_string($fb2Content);
$imageId = $xml->xpath('//fb:image/@href');
$binaryData = $xml->xpath("//fb:binary[@id='{$imageId}']");
```

**Process:**
1. Parses FB2 XML structure
2. Finds cover image reference in `<coverpage>` element
3. Extracts base64-encoded binary data
4. Decodes and saves as image file

### Dynamic Cover Generation
```php
// Creates beautiful placeholders from metadata
$cover = $dynamicGenerator->generatePlaceholderCover(
    title: "My Book Title",
    author: "John Doe",
    genre: "fiction",
    format: "epub"
);
```

**Features:**
- **Genre-based colors:**
  - Fiction: Blue gradients
  - Fantasy: Purple gradients
  - Romance: Pink gradients
  - Horror: Dark red to black
  - Sci-Fi: Cyan to dark blue
  - Mystery: Gray to black
  - History: Tan to brown
  - Biography: Light blue to navy

- **Automatic text wrapping:** Handles long titles intelligently
- **Font sizing:** Automatically adjusts to fit space
- **Visual elements:**
  - Title with shadow effects
  - Author attribution ("by Author Name")
  - Format badge (EPUB, DOC, etc.)
  - Professional gradient backgrounds

## Integration Points

### Metadata Review Form
Located at: `app/Livewire/Admin/MetadataReviewForm.php`

**New Properties:**
```php
public bool $showPdfCoverButton = false;        // Existing
public bool $showUniversalCoverButton = false;  // New
```

**New Methods:**
```php
public function generateUniversalCover(): void
private function checkUniversalCoverEligibility(): void
```

**UI Changes:**
- Blue button: "Generate Cover from PDF" (PDF files only)
- Purple button: "Generate Cover Automatically" (other formats)

### File Source Tracking
Cover images are tagged by their source:
- `pdf_auto_generated` - PDF first page extraction
- `epub_auto_extracted` - EPUB embedded cover
- `djvu_auto_extracted` - DJVU first page
- `fb2_auto_extracted` - FB2 embedded image
- `dynamically_generated` - Metadata-based placeholder

## Usage Examples

### Example 1: Processing an EPUB File
```php
$extractor = new UniversalCoverExtractorService();
$metadata = [
    'title' => 'The Great Novel',
    'author' => 'Jane Smith',
    'genre' => 'fiction'
];

$coverPath = $extractor->extractOrGenerateCover(
    filePath: '/path/to/book.epub',
    fileName: 'book.epub',
    metadata: $metadata
);
// Returns: '/path/to/extracted/cover.png'
```

### Example 2: Processing a TXT File
```php
$extractor = new UniversalCoverExtractorService();
$metadata = [
    'title' => 'My Essay',
    'author' => 'Student Name',
    'genre' => 'academic'
];

$coverPath = $extractor->extractOrGenerateCover(
    filePath: '/path/to/essay.txt',
    fileName: 'essay.txt',
    metadata: $metadata
);
// Returns: '/path/to/generated/dynamic_cover.png'
```

### Example 3: From Metadata Review Form
Users can click the appropriate button in the admin interface:

**For PDF files:**
1. User sees "Generate Cover from PDF" button
2. Clicks button → `generatePdfCover()` method called
3. First page extracted using existing PDF service
4. Cover saved to database and storage

**For other formats:**
1. User sees "Generate Cover Automatically" button
2. Clicks button → `generateUniversalCover()` method called
3. Appropriate extraction method selected based on format
4. Cover saved to database and storage

## Requirements

### System Requirements

**For PDF extraction (existing):**
- Imagick PHP extension OR
- ImageMagick command-line tool
- Ghostscript (for PDF processing on Windows)

**For DJVU extraction:**
- `ddjvu` command-line tool (from djvulibre package) OR
- ImageMagick with DJVU support

**For EPUB extraction:**
- PHP ZipArchive extension (usually enabled by default)

**For dynamic generation:**
- PHP GD library (usually enabled by default)
- TrueType font file (auto-detects from system fonts)

### Optional Requirements
- TTF fonts for better typography (service auto-detects common locations)

## Configuration

### Font Configuration
The `DynamicCoverGeneratorService` automatically searches for fonts in these locations:
```php
[
    __DIR__ . '/../../resources/fonts/OpenSans-Regular.ttf',  // Project fonts
    'C:\\Windows\\Fonts\\arial.ttf',                         // Windows
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',        // Linux
    '/System/Library/Fonts/Helvetica.ttc',                    // macOS
]
```

### Color Schemes
Genre color schemes are defined in `getGenreColor()` method. Add new genres by extending the array:

```php
$colorSchemes = [
    'fiction' => ['start' => [70, 130, 180], 'end' => [25, 25, 112]],
    'fantasy' => ['start' => [138, 43, 226], 'end' => [75, 0, 130]],
    // Add your custom genres here
];
```

## Troubleshooting

### Issue: "Failed to generate cover" for EPUB
**Cause:** No cover image found in EPUB structure
**Solution:** System falls back to dynamic generation automatically

### Issue: "ddjvu command not found" for DJVU
**Cause:** djvulibre package not installed
**Solution:** Install djvulibre or ensure ImageMagick has DJVU support

### Issue: Generated covers have no text
**Cause:** TTF font not found
**Solution:** Ensure system fonts are available or place a font file in `resources/fonts/`

### Issue: PDF extraction stopped working
**Cause:** This should not happen - PDF extraction uses existing service
**Solution:** Check Imagick/ImageMagick installation (unchanged from before)

## Backward Compatibility

✅ **100% backward compatible with existing PDF extraction**

- PDF files still use the original `PdfCoverExtractorService`
- No database migrations required
- No changes to existing API or UI for PDF handling
- Existing tests and validation remain unchanged
- User experience for PDF files is identical

## Future Enhancements

Potential areas for expansion:
1. **Custom templates:** Allow admin-defined cover templates
2. **Batch processing:** Generate covers for multiple files at once
3. **AI generation:** Integration with image generation APIs
4. **User uploads:** Allow users to upload custom cover images
5. **Cover editing:** Basic image editing capabilities
6. **More formats:** Support for MOBI, AZW, and other ebook formats

## File Locations

**Services:**
- `app/Services/PdfCoverExtractorService.php` (existing)
- `app/Services/DynamicCoverGeneratorService.php` (new)
- `app/Services/UniversalCoverExtractorService.php` (new)

**Components:**
- `app/Livewire/Admin/MetadataReviewForm.php` (updated)

**Views:**
- `resources/views/livewire/admin/metadata-review-form.blade.php` (updated)

**Documentation:**
- `docs/universal-cover-extraction.md` (this file)

## Support

For issues or questions about the universal cover extraction system:
1. Check the troubleshooting section above
2. Review Laravel logs for detailed error messages
3. Ensure all system requirements are met
4. Verify file permissions on storage directories

---

**Version:** 1.0
**Last Updated:** 2026-05-11
**Maintained by:** Development Team
