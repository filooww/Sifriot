<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfCoverExtractorService
{
    /**
     * Extract the first page of a PDF and save it as an image.
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @param  string  $outputPath  Path to save the image (without extension)
     * @return string|null  Path to the saved image or null on failure
     */
    public function extractFirstPage(string $pdfPath, string $outputPath): ?string
    {
        if (! file_exists($pdfPath)) {
            Log::error('PDF file not found', ['path' => $pdfPath]);
            return null;
        }

        try {
            // Try Imagick first (preferred for quality)
            if ($this->isImagickAvailable()) {
                return $this->extractWithImagick($pdfPath, $outputPath);
            }

            // Fallback to ImageMagick convert command
            if ($this->isGdAvailable()) {
                return $this->extractWithGd($pdfPath, $outputPath);
            }

            // Final fallback to FFmpeg
            Log::info('Trying FFmpeg as fallback for PDF extraction');
            return $this->extractWithFfmpeg($pdfPath, $outputPath);
        } catch (\Exception $e) {
            Log::error('Failed to extract PDF cover', [
                'path' => $pdfPath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if Imagick extension is available.
     */
    private function isImagickAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    /**
     * Check if ImageMagick convert command is available.
     */
    private function isGdAvailable(): bool
    {
        $command = 'convert -version';
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Check if FFmpeg command is available.
     */
    private function isFfmpegAvailable(): bool
    {
        $command = 'ffmpeg -version';
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Extract first page using Imagick.
     */
    private function extractWithImagick(string $pdfPath, string $outputPath): ?string
    {
        try {
            $imagick = new \Imagick($pdfPath.'[0]'); // [0] = first page only

            // Set resolution for better quality
            $imagick->setResolution(150, 150);

            // Set format to PNG
            $imagick->setImageFormat('png');

            // Optimize
            $imagick->setImageCompressionQuality(90);
            $imagick->stripImage();

            // Save the image
            $imagePath = $outputPath . '.png';
            $imagick->writeImage($imagePath);
            $imagick->clear();
            $imagick->destroy();

            Log::info('PDF cover extracted with Imagick', [
                'pdf' => basename($pdfPath),
                'image' => $imagePath,
            ]);

            return $imagePath;
        } catch (\Exception $e) {
            Log::error('Imagick PDF extraction failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract first page using GD with ImageMagick convert command.
     */
    private function extractWithGd(string $pdfPath, string $outputPath): ?string
    {
        try {
            // Use ImageMagick's convert command via exec
            $imagePath = $outputPath . '.png';
            $command = escapeshellcmd("convert -density 150 -quality 90 '{$pdfPath}[0]' {$imagePath}");

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($imagePath)) {
                Log::error('ImageMagick convert command failed', [
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                ]);
                return null;
            }

            Log::info('PDF cover extracted with ImageMagick convert', [
                'pdf' => basename($pdfPath),
                'image' => $imagePath,
            ]);

            return $imagePath;
        } catch (\Exception $e) {
            Log::error('GD/ImageMagick convert PDF extraction failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract first page using FFmpeg command (fallback option).
     */
    private function extractWithFfmpeg(string $pdfPath, string $outputPath): ?string
    {
        try {
            // Use FFmpeg's command via exec
            $imagePath = $outputPath . '.png';
            $command = escapeshellcmd("ffmpeg -i '{$pdfPath}' -vframes 1 -q:v 2 {$imagePath}");

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($imagePath)) {
                Log::error('FFmpeg command failed', [
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                ]);
                return null;
            }

            Log::info('PDF cover extracted with FFmpeg', [
                'pdf' => basename($pdfPath),
                'image' => $imagePath,
            ]);

            return $imagePath;
        } catch (\Exception $e) {
            Log::error('FFmpeg PDF extraction failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if a file is a PDF.
     */
    public function isPdf(string $filePath): bool
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Generate a unique filename for the cover image.
     */
    public function generateCoverFilename(string $pdfFilename): string
    {
        $extension = 'png';
        $baseName = pathinfo($pdfFilename, PATHINFO_FILENAME);

        return uniqid($baseName . '_cover_', true) . '.' . $extension;
    }
}
