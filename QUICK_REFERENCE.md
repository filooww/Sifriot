# Quick Reference: Cover Extraction & Generation System

## How It Works Now

### Automatic Flow
```
File Scanning → Metadata Extraction → Cover Extraction → Cover Storage
     ↓              ↓                   ↓                  ↓
  File Found    Extract Title/     Generate/Extract    Save to Database
               Author/Publisher   Cover Image         & Storage
```

### Key Features

#### 1. File-Type-Based Colors
- **PDF**: Deep Blue (#1e3a8a → #1e40af)
- **EPUB/MOBI**: Purple (#6b21a8 → #7c3aed) 
- **DOCX/DOC**: Orange (#ea580c → #f97316)
- **TXT**: Gray (#4b5563 → #6b7280)
- **DJVU**: Cyan (#06b6d4 → #0891b2)
- **FB2**: Green (#22c55e → #16a34a)

#### 2. Supported Formats
**Extracted Covers:**
- PDF (first page)
- EPUB (embedded cover)
- DJVU (first page)
- FB2 (embedded cover)
- CBZ/CBR/CB7 (comic archives)

**Generated Covers:**
- DOCX, DOC, ODT, RTF, TXT
- Any unsupported format

#### 3. Cover Storage
- **Location**: `storage/app/public/covers/`
- **Database**: `files` table with `file_type = 'cover'`
- **Access**: Via web routes `/covers/{publication}/{filename}`

## Usage

### Run Metadata Extraction (with automatic covers)
```bash
# Single file
php artisan metadata:extract-all --limit=1

# Batch processing
php artisan metadata:extract-all --limit=100 --chunk-size=20

# Force re-extraction
php artisan metadata:extract-all --force

# Filter by content type
php artisan metadata:extract-all --content-type=1
```

### Monitor Queue Workers
```bash
# Start queue worker
php artisan queue:work -v

# Monitor logs
tail -f storage/logs/folder_scan.log
```

### Verify Results
```bash
# Check cover files
ls -la storage/app/public/covers/

# Check database
php artisan tinker
>>> File::where('file_type', 'cover')->count()
>>> File::where('file_type', 'cover')->latest()->first()
```

## Troubleshooting

### Common Issues

**1. Covers not being generated**
- Check queue worker is running: `php artisan queue:work -v`
- Check logs: `tail -f storage/logs/folder_scan.log`
- Verify metadata extraction succeeded first

**2. Poor quality PDF covers**
- Ensure ImageMagick or Imagick is installed
- Check PDF file permissions
- Verify Ghostscript is available (for Imagick)

**3. Font rendering issues**
- System will automatically fall back to available fonts
- Check font permissions on your system
- Verify GD library is properly installed

**4. Storage permission errors**
```bash
# Fix storage permissions
chmod -R 775 storage/app/public/covers/
chown -R www-data:www-data storage/app/public/
```

### Log Messages

**Success:**
```
[INFO] Starting cover extraction
[INFO] Cover extraction completed successfully
[INFO] Cover file saved successfully
```

**Fallback:**
```
[INFO] Cover extraction failed, generating dynamic fallback
[INFO] No cover found in EPUB, will generate placeholder
```

**Errors:**
```
[ERROR] Cover extraction error
[WARNING] No suitable font found for cover generation
```

## Configuration

### Adjust Cover Quality
Edit `app/Services/PdfCoverExtractorService.php`:
```php
$imagick->setResolution(150, 150); // Increase for better quality
$imagick->setImageCompressionQuality(90); // Adjust compression
```

### Customize Colors
Edit `app/Services/DynamicCoverGeneratorService.php`:
```php
'pdf' => ['start' => [R, G, B], 'end' => [R, G, B]], // Customize RGB values
```

### Add New Formats
Edit `app/Services/UniversalCoverExtractorService.php`:
```php
$newformat => $this->extractNewFormatCover($filePath, $fileName),
```

## Performance Tips

1. **Batch Size**: Use `--chunk-size=20` for optimal performance
2. **Queue Workers**: Run multiple workers: `php artisan queue:work --daemon`
3. **Storage**: Use SSD for better cover generation performance
4. **Memory**: Increase PHP memory limit for large PDF processing

## Monitoring

### Check System Health
```bash
# Queue status
php artisan queue:monitor

# Failed jobs
php artisan queue:failed

# Clear stuck jobs
php artisan queue:flush
```

### Statistics
```php
// In Tinker
$totalFiles = File::where('file_type', 'content')->count();
$totalCovers = File::where('file_type', 'cover')->count();
$coverage = ($totalCovers / $totalFiles) * 100;
echo "Cover coverage: {$coverage}%";
```

## Integration Points

### Manual Cover Upload (Still Works)
- Admin interface still supports manual cover upload
- Auto-generated covers can be replaced manually
- Both methods coexist seamlessly

### API Integration
```php
// Programmatic cover generation
$coverExtractor = app(UniversalCoverExtractorService::class);
$coverPath = $coverExtractor->extractOrGenerateCover(
    $filePath,
    $fileName,
    $metadata
);
```

## Maintenance

### Clean Up Old Covers
```bash
# Remove covers for deleted publications
php artisan tinker
>>> File::where('file_type', 'cover')
>>>    ->whereDoesntHave('publication')
>>>    ->delete();
```

### Regenerate Covers
```bash
# Force cover regeneration
php artisan metadata:extract-all --force --limit=100
```

## Support

### Debug Mode
Enable detailed logging in `.env`:
```
LOG_LEVEL=debug
```

### Check Dependencies
```bash
# ImageMagick
convert -version

# PHP GD
php -m | grep gd

# PHP Imagick
php -m | grep imagick
```