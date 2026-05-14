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
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $coverPath = match ($extension) {
            'pdf' => $this->pdfExtractor->extractFirstPage($filePath, $this->getTempPath($fileName)),
            'epub' => $this->extractEpubCover($filePath, $fileName),
            'djvu' => $this->extractDjvuCover($filePath, $fileName),
            'fb2' => $this->extractFb2Cover($filePath, $fileName),
            'doc', 'docx' => $this->generateDynamicCover($fileName, $metadata),
            'txt' => $this->generateDynamicCover($fileName, $metadata),
            default => $this->generateDynamicCover($fileName, $metadata),
        };

        // Fallback to dynamic cover generation if extraction failed
        if ($coverPath === null && in_array($extension, ['pdf', 'epub', 'djvu', 'fb2'])) {
            Log::info('Cover extraction failed, generating dynamic fallback', [
                'file' => $fileName,
                'format' => $extension,
            ]);
            return $this->generateDynamicCover($fileName, $metadata);
        }

        return $coverPath;
    }

    /**
     * Extract cover from EPUB file (EPUB is a ZIP archive).
     */
    private function extractEpubCover(string $epubPath, string $fileName): ?string
    {
        try {
            $zip = new ZipArchive();

            if ($zip->open($epubPath) !== TRUE) {
                throw new \Exception('Cannot open EPUB file');
            }

            // Common cover locations in EPUB
            $coverPaths = [
                'OEBPS/cover.jpg',
                'OEBPS/cover.png',
                'OEBPS/Images/cover.jpg',
                'OEBPS/Images/cover.png',
                'cover.jpg',
                'cover.png',
                'Images/cover.jpg',
                'Images/cover.png',
            ];

            // Try to find cover
            $coverPath = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];

                if (preg_match('/cover\.(jpg|jpeg|png|gif)$/i', $name)) {
                    $coverPath = $name;
                    break;
                }
            }

            // Try extracting cover
            if ($coverPath && $zip->extractTo(storage_path('app/temp/'), $coverPath)) {
                $extractedPath = storage_path('app/temp/' . basename($coverPath));

                if (file_exists($extractedPath)) {
                    $finalPath = $this->getTempPath($fileName);
                    rename($extractedPath, $finalPath . '.' . pathinfo($coverPath, PATHINFO_EXTENSION));

                    $zip->close();
                    Log::info('EPUB cover extracted', ['file' => $fileName]);
                    return $finalPath . '.' . pathinfo($coverPath, PATHINFO_EXTENSION);
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
     * Extract cover from DJVU file.
     */
    private function extractDjvuCover(string $djvuPath, string $fileName): ?string
    {
        try {
            $outputPath = $this->getTempPath($fileName);

            // Try using ddjvu command
            $command = sprintf(
                'ddjvu -format=png -page=1 -quality=90 "%s" "%s.png"',
                $djvuPath,
                $outputPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath . '.png')) {
                Log::info('DJVU cover extracted', ['file' => $fileName]);
                return $outputPath . '.png';
            }

            Log::warning('ddjvu command failed, trying alternate method', ['file' => $fileName]);

            // Try using ImageMagick if available
            $magickCommand = sprintf(
                'convert "%s[0]" "%s.png"',
                $djvuPath,
                $outputPath
            );

            exec($magickCommand, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath . '.png')) {
                Log::info('DJVU cover extracted with ImageMagick', ['file' => $fileName]);
                return $outputPath . '.png';
            }

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
     */
    private function extractFb2Cover(string $fb2Path, string $fileName): ?string
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

            // Register namespace
            $xml->registerXPathNamespace('fb', 'http://www.gribuser.ru/xml/fictionbook/2.0');

            // Find coverpage element
            $coverpage = $xml->xpath('//fb:description/fb:title-info/fb:coverpage');
            if (empty($coverpage)) {
                Log::info('No cover found in FB2', ['file' => $fileName]);
                return null;
            }

            // Find image reference
            $imageRef = $coverpage[0]->xpath('//fb:image');
            if (empty($imageRef)) {
                return null;
            }

            $imageId = (string) $imageRef[0]['href'];
            $imageId = ltrim($imageId, '#');

            // Find binary data
            $binary = $xml->xpath("//fb:binary[@id='{$imageId}']");
            if (empty($binary)) {
                return null;
            }

            $imageData = base64_decode((string) $binary[0]);
            if (!$imageData) {
                return null;
            }

            // Save image
            $outputPath = $this->getTempPath($fileName);
            $extension = $this->detectImageExtension($imageData);

            file_put_contents($outputPath . '.' . $extension, $imageData);

            Log::info('FB2 cover extracted', ['file' => $fileName]);
            return $outputPath . '.' . $extension;

        } catch (\Exception $e) {
            Log::error('FB2 cover extraction failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);
            return null;
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
            'pdf', 'epub', 'djvu', 'fb2', 'doc', 'docx', 'txt'
        ];

        return in_array($extension, $supportedExtensions);
    }
}
