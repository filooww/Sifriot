# PDF Cover Extraction Setup

The PDF cover extraction feature requires one of the following tools to be installed:

## Option 1: Install Imagick PHP Extension (Recommended)

### For Laragon on Windows:

1. **Download Imagick for PHP 8.3**
   - Visit: https://windows.php.net/downloads/pecl/releases/imagick/
   - Download the latest version for PHP 8.3 (x64)
   - Example: `imagick-3.7.0-8.3-vs16-x64.zip`

2. **Install the extension**
   ```bash
   # Extract the downloaded zip file
   # Copy php_imagick.dll to your PHP extensions folder
   # Copy all DLL files to your PHP root folder
   
   # Typical paths for Laragon:
   # PHP extensions: C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\ext\
   # PHP root: C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\
   ```

3. **Enable the extension in php.ini**
   ```ini
   extension=imagick
   ```

4. **Restart Laragon**

5. **Verify installation**
   ```bash
   php -m | grep imagick
   ```

## Option 2: Install ImageMagick

### For Windows:

1. **Download ImageMagick**
   - Visit: https://imagemagick.org/script/download.php#windows
   - Download the latest x64 version (e.g., ImageMagick-7.1.2-Q16-HDRI)

2. **Install ImageMagick**
   - Run the installer
   - **Important**: Check "Add application directory to your system path"
   - Complete the installation

3. **Restart your terminal/command prompt**

4. **Verify installation**
   ```bash
   magick -version
   ```
   Note: ImageMagick 7+ uses `magick` instead of `convert` to avoid conflicts with Windows' built-in convert.exe

5. **If not in PATH, update the service**
   The PdfCoverExtractorService will automatically detect ImageMagick in these locations:
   - `magick` (if in PATH)
   - `convert` (legacy ImageMagick 6)
   - `C:\Program Files\ImageMagick-7.1.2-Q16-HDRI\magick.exe` (default Windows path)

## Testing the Installation

After installing either tool, test the PDF cover extraction:

1. Go to your metadata review form
2. Click the "Generate Cover from PDF" button
3. Check the success message

## Troubleshooting

### Imagick not working:
- Ensure you downloaded the correct version for your PHP version
- Check that php_imagick.dll is in the ext folder
- Verify that all DLL files are in the PHP root folder
- Restart Laragon after making changes

### ImageMagick not working:
- Ensure the installation directory is in your system PATH
- Try running `magick -version` in command prompt (ImageMagick 7+)
- For ImageMagick 6, try `convert -version`
- You may need to restart your computer after installation
- Check the service is looking in the correct installation directory

### Still having issues:
Check the Laravel logs for detailed error messages:
```bash
tail -f storage/logs/laravel.log
```

## Alternative: Manual Cover Upload

If you cannot install these tools, you can still add cover images manually:
1. In the metadata review form, use the "Choose File" button under "Cover Image"
2. Select an image file (JPG, PNG, or WebP)
3. The image will be automatically saved when you select it