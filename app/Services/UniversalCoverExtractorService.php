<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UniversalCoverExtractorService
{
    private PdfCoverExtractorService $pdfExtractor;
    private DynamicCoverGeneratorService $dynamicGenerator;

    public function __construct()
    {
        $this->pdfExtractor = new PdfCoverExtractorService();
        $this->dynamicGenerator = new DynamicCoverGeneratorService();
    }

    /**
     * Extract or generate cover image for various formats.
     *
     * @param  string  $filePath  Path to the file
     * @param  string  $fileName  Original filename
     * @param  array  $metadata  Extracted metadata (title, author, genre)
     * @return string|null  Path to generated cover or null on failure
     */
    public function extractOrGenerateCover(string $filePath, string $fileName, array $metadata = []): ?string
    {
        try {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            Log::info('Starting cover extraction/generation', [
                'file' => $fileName,
                'format' => $extension,
                'has_metadata' => !empty($metadata),
            ]);

            // For FB2 files, enhance metadata if not provided
            if ($extension === 'fb2' && empty($metadata)) {
                $metadata = $this->extractFb2Metadata($filePath);
            }

            $coverPath = match ($extension) {
                'pdf' => $this->extractPdfCover($filePath, $fileName),
                'epub' => $this->extractEpubCover($filePath, $fileName),
                'djvu' => $this->extractDjvuCover($filePath, $fileName),
                'fb2' => $this->extractFb2Cover($filePath, $fileName, $metadata),
                'mobi', 'azw', 'azw3' => $this->extractKindleCover($filePath, $fileName),
                'doc', 'docx', 'odt', 'rtf' => $this->generateDynamicCover($fileName, $metadata),
                'txt', 'rtf' => $this->generateDynamicCover($fileName, $metadata),
                'cbr', 'cbz', 'cb7' => $this->extractComicCover($filePath, $fileName),
                default => $this->generateDynamicCover($fileName, $metadata),
            };

            // Fallback to dynamic cover generation if extraction failed
            if ($coverPath === null) {
                $metadataInfo = [
                    'has_title' => !empty($metadata['title']),
                    'has_author' => !empty($metadata['author']),
                    'has_genre' => !empty($metadata['genre']),
                ];

                Log::info('Cover extraction failed, generating dynamic fallback', [
                    'file' => $fileName,
                    'format' => $extension,
                    'metadata_available' => $metadataInfo,
                ]);
                return $this->generateDynamicCover($fileName, $metadata);
            }

            Log::info('Cover extraction/generation successful', [
                'file' => $fileName,
                'cover_path' => $coverPath,
            ]);

            return $coverPath;

        } catch (\Exception $e) {
            Log::error('Cover extraction/generation failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Try dynamic generation as ultimate fallback
            return $this->generateDynamicCover($fileName, $metadata);
        }
    }

    /**
     * Extract cover from PDF with enhanced error handling.
     */
    private function extractPdfCover(string $pdfPath, string $fileName): ?string
    {
        try {
            return $this->pdfExtractor->extractFirstPage($pdfPath, $this->getTempPath($fileName));
        } catch (\Exception $e) {
            Log::error('PDF cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from Kindle formats (MOBI, AZW, AZW3).
     */
    private function extractKindleCover(string $kindlePath, string $fileName): ?string
    {
        try {
            $outputPath = $this->getTempPath($fileName);

            // Try using kindleunpack or other tools if available
            $command = sprintf(
                'ebook-meta "%s" 2>&1 | grep -i cover',
                $kindlePath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                Log::info('Kindle cover metadata found', ['file' => $fileName]);
                // For now, return null and let dynamic generation handle it
                // Full implementation would require kindleunpack or similar tools
                return null;
            }

            Log::info('No Kindle cover extraction tools available, will generate placeholder', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('Kindle cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from comic formats (CBR, CBZ, CB7).
     */
    private function extractComicCover(string $comicPath, string $fileName): ?string
    {
        try {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $outputPath = $this->getTempPath($fileName);

            if ($extension === 'cbz') {
                // CBZ is just a ZIP file
                return $this->extractZipCover($comicPath, $fileName, $outputPath);
            } elseif ($extension === 'cbr') {
                // CBR is a RAR file - need unrar command
                return $this->extractRarCover($comicPath, $fileName, $outputPath);
            } elseif ($extension === 'cb7') {
                // CB7 is a 7Z file - need 7z command
                return $this->extractSevenZipCover($comicPath, $fileName, $outputPath);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Comic cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from ZIP file (CBZ).
     */
    private function extractZipCover(string $zipPath, string $fileName, string $outputPath): ?string
    {
        try {
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== TRUE) {
                throw new \Exception('Cannot open ZIP file');
            }

            // Look for first image file (usually the cover)
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];

                // Check if it's an image file
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $name)) {
                    // Extract the first image found
                    if ($zip->extractTo(storage_path('app/temp/'), $name)) {
                        $extractedPath = storage_path('app/temp/' . basename($name));

                        if (file_exists($extractedPath)) {
                            $finalPath = $outputPath . '.' . pathinfo($name, PATHINFO_EXTENSION);
                            rename($extractedPath, $finalPath);

                            $zip->close();
                            Log::info('ZIP cover extracted', ['file' => $fileName]);
                            return $finalPath;
                        }
                    }
                    break; // Use first image found
                }
            }

            $zip->close();
            Log::info('No cover found in ZIP file', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('ZIP cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from RAR file (CBR).
     */
    private function extractRarCover(string $rarPath, string $fileName, string $outputPath): ?string
    {
        try {
            // Try using unrar command
            $command = sprintf(
                'unrar lb "%s" | head -1',
                $rarPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $firstFile = trim($output[0]);

                // Check if it's an image
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $firstFile)) {
                    $extractCommand = sprintf(
                        'unrar p -inul "%s" "%s" > "%s.jpg"',
                        $rarPath,
                        $firstFile,
                        $outputPath
                    );

                    exec($extractCommand, $extractOutput, $extractReturnCode);

                    if ($extractReturnCode === 0 && file_exists($outputPath . '.jpg')) {
                        Log::info('RAR cover extracted', ['file' => $fileName]);
                        return $outputPath . '.jpg';
                    }
                }
            }

            Log::info('No RAR extraction tools available, will generate placeholder', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('RAR cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from 7Z file (CB7).
     */
    private function extractSevenZipCover(string $sevenZipPath, string $fileName, string $outputPath): ?string
    {
        try {
            // Try using 7z command
            $command = sprintf(
                '7z l "%s" | grep -E "\.(jpg|jpeg|png|gif|webp)$" | head -1',
                $sevenZipPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                // Extract the first image found
                $extractCommand = sprintf(
                    '7z e -so "%s" > "%s.jpg"',
                    $sevenZipPath,
                    $outputPath
                );

                exec($extractCommand, $extractOutput, $extractReturnCode);

                if ($extractReturnCode === 0 && file_exists($outputPath . '.jpg')) {
                    Log::info('7Z cover extracted', ['file' => $fileName]);
                    return $outputPath . '.jpg';
                }
            }

            Log::info('No 7Z extraction tools available, will generate placeholder', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('7Z cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from EPUB file (EPUB is a ZIP archive) with enhanced error handling.
     */
    private function extractEpubCover(string $epubPath, string $fileName): ?string
    {
        try {
            $zip = new ZipArchive();

            if ($zip->open($epubPath) !== TRUE) {
                Log::warning('Cannot open EPUB file', ['file' => $fileName]);
                return null;
            }

            // Enhanced cover location patterns
            $coverPatterns = [
                '/cover\.(jpg|jpeg|png|gif|webp)$/i',
                '/OEBPS\/cover\.(jpg|jpeg|png|gif|webp)$/i',
                '/OEBPS\/Images\/cover\.(jpg|jpeg|png|gif|webp)$/i',
                '/Images\/cover\.(jpg|jpeg|png|gif|webp)$/i',
                '/item\/image\/cover\.(jpg|jpeg|png|gif|webp)$/i',
            ];

            // Try to find cover using patterns
            $coverPath = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];

                foreach ($coverPatterns as $pattern) {
                    if (preg_match($pattern, $name)) {
                        $coverPath = $name;
                        break 2;
                    }
                }
            }

            // Try extracting cover
            if ($coverPath && $zip->extractTo(storage_path('app/temp/'), $coverPath)) {
                $extractedPath = storage_path('app/temp/' . basename($coverPath));

                if (file_exists($extractedPath)) {
                    $finalPath = $this->getTempPath($fileName);
                    $extension = pathinfo($coverPath, PATHINFO_EXTENSION);
                    rename($extractedPath, $finalPath . '.' . $extension);

                    $zip->close();
                    Log::info('EPUB cover extracted successfully', [
                        'file' => $fileName,
                        'cover_path' => $coverPath,
                    ]);
                    return $finalPath . '.' . $extension;
                }
            }

            $zip->close();
            Log::info('No cover found in EPUB, will generate placeholder', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('EPUB cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from DJVU file with enhanced error handling.
     */
    private function extractDjvuCover(string $djvuPath, string $fileName): ?string
    {
        try {
            $outputPath = $this->getTempPath($fileName);

            // Try using ddjvu command
            $command = sprintf(
                'ddjvu -format=png -page=1 -quality=90 -scale=100 "%s" "%s.png"',
                $djvuPath,
                $outputPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath . '.png')) {
                Log::info('DJVU cover extracted with ddjvu', ['file' => $fileName]);
                return $outputPath . '.png';
            }

            Log::info('ddjvu command failed, trying ImageMagick', ['file' => $fileName]);

            // Try using ImageMagick if available
            $magickCommand = sprintf(
                'convert "%s[0]" -density 150 -quality 90 "%s.png"',
                $djvuPath,
                $outputPath
            );

            exec($magickCommand, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath . '.png')) {
                Log::info('DJVU cover extracted with ImageMagick', ['file' => $fileName]);
                return $outputPath . '.png';
            }

            Log::info('All DJVU extraction methods failed', ['file' => $fileName]);
            return null;

        } catch (\Exception $e) {
            Log::error('DJVU cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract cover from FB2 file ( FictionBook format).
     * Falls back to dynamic cover generation if no embedded image is found.
     */
    private function extractFb2Cover(string $fb2Path, string $fileName, array $metadata = []): ?string
    {
        try {
            $xmlContent = file_get_contents($fb2Path);
            if (!$xmlContent) {
                throw new \Exception('Cannot read FB2 file');
            }

            // Parse XML to find cover image
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                throw new \Exception('Invalid FB2 XML');
            }

            // Detect all namespaces
            $namespaces = $xml->getNamespaces(true);
            $fbNamespace = $namespaces[''] ?? 'http://www.gribuser.ru/xml/fictionbook/2.0';

            // Register namespace with a consistent prefix
            $xml->registerXPathNamespace('fb', $fbNamespace);

            // Try multiple approaches to find coverpage
            $coverpage = null;

            // Try with explicit namespace first
            try {
                $coverpage = $xml->xpath('//fb:description/fb:title-info/fb:coverpage');
            } catch (\Exception $e) {
                Log::debug('Explicit namespace xpath failed', ['error' => $e->getMessage()]);
            }

            // Fallback to local-name() approach (namespace-agnostic)
            if (!$coverpage || empty($coverpage)) {
                try {
                    $coverpage = $xml->xpath('//*[local-name()="description"]/*[local-name()="title-info"]/*[local-name()="coverpage"]');
                } catch (\Exception $e) {
                    Log::debug('Local-name xpath failed', ['error' => $e->getMessage()]);
                }
            }

            // Final fallback to direct path without namespace
            if (!$coverpage || empty($coverpage)) {
                try {
                    $coverpage = $xml->xpath('//description/title-info/coverpage');
                } catch (\Exception $e) {
                    Log::debug('Direct xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (empty($coverpage)) {
                Log::info('No coverpage element found in FB2, will use dynamic fallback', ['file' => $fileName]);
                return null; // Will trigger fallback
            }

            // Find image reference - try multiple approaches
            $imageRef = null;

            // Try relative xpath from coverpage with namespace
            try {
                $imageRef = $coverpage[0]->xpath('.//*[local-name()="image"]');
            } catch (\Exception $e) {
                Log::debug('Relative image xpath failed', ['error' => $e->getMessage()]);
            }

            // Try with explicit namespace
            if (!$imageRef || empty($imageRef)) {
                try {
                    $imageRef = $coverpage[0]->xpath('.//fb:image');
                } catch (\Exception $e) {
                    Log::debug('Namespace image xpath failed', ['error' => $e->getMessage()]);
                }
            }

            // Try direct approach
            if (!$imageRef || empty($imageRef)) {
                try {
                    $imageRef = $coverpage[0]->xpath('.//image');
                } catch (\Exception $e) {
                    Log::debug('Direct image xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (empty($imageRef)) {
                Log::info('No image reference found in FB2 coverpage, will use dynamic fallback', ['file' => $fileName]);
                return null; // Will trigger fallback
            }

            $imageId = (string) $imageRef[0]['href'];
            $imageId = ltrim($imageId, '#');

            if (empty($imageId)) {
                Log::info('Empty image ID in FB2 coverpage, will use dynamic fallback', ['file' => $fileName]);
                return null;
            }

            // Find binary data - try multiple approaches
            $binary = null;

            // Try with local-name() approach
            try {
                $binary = $xml->xpath("//*[local-name()='binary'][@id='{$imageId}']");
            } catch (\Exception $e) {
                Log::debug('Local-name binary xpath failed', ['error' => $e->getMessage()]);
            }

            // Try with explicit namespace
            if (!$binary || empty($binary)) {
                try {
                    $binary = $xml->xpath("//fb:binary[@id='{$imageId}']");
                } catch (\Exception $e) {
                    Log::debug('Namespace binary xpath failed', ['error' => $e->getMessage()]);
                }
            }

            // Try direct approach
            if (!$binary || empty($binary)) {
                try {
                    $binary = $xml->xpath("//binary[@id='{$imageId}']");
                } catch (\Exception $e) {
                    Log::debug('Direct binary xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (empty($binary)) {
                Log::info('No binary data found for FB2 cover image, will use dynamic fallback', ['file' => $fileName]);
                return null; // Will trigger fallback
            }

            $imageData = base64_decode((string) $binary[0]);
            if (!$imageData) {
                Log::info('Failed to decode FB2 cover image data, will use dynamic fallback', ['file' => $fileName]);
                return null; // Will trigger fallback
            }

            // Save image
            $outputPath = $this->getTempPath($fileName);
            $extension = $this->detectImageExtension($imageData);

            file_put_contents($outputPath . '.' . $extension, $imageData);

            Log::info('FB2 cover extracted successfully', ['file' => $fileName]);
            return $outputPath . '.' . $extension;

        } catch (\Exception $e) {
            Log::error('FB2 cover extraction failed, will use dynamic fallback', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null; // Will trigger fallback
        }
    }

    /**
     * Extract metadata from FB2 file for better fallback cover generation.
     */
    private function extractFb2Metadata(string $fb2Path): array
    {
        try {
            $xmlContent = file_get_contents($fb2Path);
            if (!$xmlContent) {
                return [];
            }

            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                return [];
            }

            // Detect all namespaces
            $namespaces = $xml->getNamespaces(true);
            $fbNamespace = $namespaces[''] ?? 'http://www.gribuser.ru/xml/fictionbook/2.0';

            // Register namespace with a consistent prefix
            $xml->registerXPathNamespace('fb', $fbNamespace);

            $metadata = [];

            // Extract title - try multiple approaches
            $title = null;

            // Try with explicit namespace first
            try {
                $title = $xml->xpath('//fb:description/fb:title-info/fb:book-title');
            } catch (\Exception $e) {
                Log::debug('Explicit namespace title xpath failed', ['error' => $e->getMessage()]);
            }

            // Fallback to local-name() approach
            if (!$title || empty($title)) {
                try {
                    $title = $xml->xpath('//*[local-name()="description"]/*[local-name()="title-info"]/*[local-name()="book-title"]');
                } catch (\Exception $e) {
                    Log::debug('Local-name title xpath failed', ['error' => $e->getMessage()]);
                }
            }

            // Final fallback to direct path
            if (!$title || empty($title)) {
                try {
                    $title = $xml->xpath('//description/title-info/book-title');
                } catch (\Exception $e) {
                    Log::debug('Direct title xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (!empty($title)) {
                $metadata['title'] = (string) $title[0];
            }

            // Extract first author - try multiple approaches
            $author = null;

            try {
                $author = $xml->xpath('//fb:description/fb:title-info/fb:author[1]');
            } catch (\Exception $e) {
                Log::debug('Explicit namespace author xpath failed', ['error' => $e->getMessage()]);
            }

            if (!$author || empty($author)) {
                try {
                    $author = $xml->xpath('//*[local-name()="description"]/*[local-name()="title-info"]/*[local-name()="author"][1]');
                } catch (\Exception $e) {
                    Log::debug('Local-name author xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (!$author || empty($author)) {
                try {
                    $author = $xml->xpath('//description/title-info/author[1]');
                } catch (\Exception $e) {
                    Log::debug('Direct author xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (!empty($author)) {
                $authorParts = [];
                foreach ($author[0]->children() as $part) {
                    $partName = $part->getName();
                    if (in_array($partName, ['first-name', 'middle-name', 'last-name'])) {
                        $authorParts[] = (string) $part;
                    }
                }
                if (!empty($authorParts)) {
                    $metadata['author'] = implode(' ', $authorParts);
                }
            }

            // Extract first genre - try multiple approaches
            $genre = null;

            try {
                $genre = $xml->xpath('//fb:description/fb:title-info/fb:genre[1]');
            } catch (\Exception $e) {
                Log::debug('Explicit namespace genre xpath failed', ['error' => $e->getMessage()]);
            }

            if (!$genre || empty($genre)) {
                try {
                    $genre = $xml->xpath('//*[local-name()="description"]/*[local-name()="title-info"]/*[local-name()="genre"][1]');
                } catch (\Exception $e) {
                    Log::debug('Local-name genre xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (!$genre || empty($genre)) {
                try {
                    $genre = $xml->xpath('//description/title-info/genre[1]');
                } catch (\Exception $e) {
                    Log::debug('Direct genre xpath failed', ['error' => $e->getMessage()]);
                }
            }

            if (!empty($genre)) {
                $metadata['genre'] = (string) $genre[0];
            }

            return $metadata;

        } catch (\Exception $e) {
            Log::warning('Failed to extract FB2 metadata for fallback', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Generate dynamic cover using metadata.
     */
    private function generateDynamicCover(string $fileName, array $metadata): ?string
    {
        try {
            $title = $metadata['title'] ?? pathinfo($fileName, PATHINFO_FILENAME);
            $author = $metadata['author'] ?? '';
            $genre = $metadata['genre'] ?? '';
            $format = pathinfo($fileName, PATHINFO_EXTENSION);

            $coverPath = $this->dynamicGenerator->generatePlaceholderCover(
                $title,
                $author,
                $genre,
                $format
            );

            if ($coverPath) {
                Log::info('Dynamic cover generated', ['file' => $fileName]);
            }

            return $coverPath;

        } catch (\Exception $e) {
            Log::error('Dynamic cover generation failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get temporary file path for cover generation.
     */
    private function getTempPath(string $fileName): string
    {
        $tempDir = storage_path('app/temp/');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir . uniqid('cover_', true) . '_' . pathinfo($fileName, PATHINFO_FILENAME);
    }

    /**
     * Detect image extension from binary data.
     */
    private function detectImageExtension(string $imageData): string
    {
        // Check magic bytes
        if (str_starts_with($imageData, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($imageData, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")) {
            return 'png';
        }
        if (str_starts_with($imageData, "GIF87a") || str_starts_with($imageData, "GIF89a")) {
            return 'gif';
        }
        if (str_starts_with($imageData, "RIFF") && substr($imageData, 8, 4) === "WEBP") {
            return 'webp';
        }

        // Default to jpg
        return 'jpg';
    }

    /**
     * Check if cover extraction is supported for file type.
     */
    public function isSupported(string $fileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $supportedExtensions = [
            'pdf', 'epub', 'djvu', 'fb2', 'mobi', 'azw', 'azw3',
            'doc', 'docx', 'odt', 'rtf', 'txt',
            'cbr', 'cbz', 'cb7'
        ];

        return in_array($extension, $supportedExtensions);
    }
}
