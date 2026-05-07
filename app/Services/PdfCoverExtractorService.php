<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfCoverExtractorService
{
    private ?string $imagemagickCommand = null;

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
                Log::info('Using Imagick for PDF extraction');
                return $this->extractWithImagick($pdfPath, $outputPath);
            }

            // Fallback to ImageMagick convert command
            if ($this->isImageMagickAvailable()) {
                Log::info('Using ImageMagick convert for PDF extraction');
                return $this->extractWithImageMagick($pdfPath, $outputPath);
            }

            // No PDF extraction tools available
            Log::error('No PDF extraction tools available. Please install Imagick PHP extension or ImageMagick.', [
                'path' => $pdfPath,
                'imagick_available' => $this->isImagickAvailable(),
                'imagemagick_available' => $this->isImageMagickAvailable(),
            ]);
            return null;
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
    private function isImageMagickAvailable(): bool
    {
        // Try both magick (ImageMagick 7+) and convert (ImageMagick 6)
        $commands = [
            'magick -version',
            'convert -version',
        ];

        foreach ($commands as $command) {
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->imagemagickCommand = str_replace(' -version', '', $command);
                return true;
            }
        }

        // Try full Windows path with proper quoting
        $windowsPath = 'C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe';
        if (file_exists($windowsPath)) {
            $command = "\"{$windowsPath}\" -version";
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->imagemagickCommand = "\"{$windowsPath}\"";
                return true;
            }
        }

        return false;
    }

    /**
     * Extract first page using Imagick.
     */
    private function extractWithImagick(string $pdfPath, string $outputPath): ?string
    {
        try {
            // Set Ghostscript path for Windows if needed
            if (PHP_OS_FAMILY === 'Windows') {
                $gsPath = '"C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe"';
                if (file_exists(str_replace('"', '', $gsPath))) {
                    putenv('GS_PATH=' . $gsPath);
                }
            }

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
     * Extract first page using ImageMagick convert command.
     */
    private function extractWithImageMagick(string $pdfPath, string $outputPath): ?string
    {
        try {
            $imagePath = $outputPath . '.png';

            // Build command for Windows
            if (PHP_OS_FAMILY === 'Windows') {
                $command = sprintf(
                    '%s -density 150 -quality 90 "%s[0]" "%s"',
                    $this->imagemagickCommand,
                    $pdfPath,
                    $imagePath
                );

                // Use proc_open for better Windows compatibility
                $descriptorspec = [
                    0 => ['pipe', 'r'],  // stdin
                    1 => ['pipe', 'w'],  // stdout
                    2 => ['pipe', 'w'],  // stderr
                ];

                $process = proc_open($command, $descriptorspec, $pipes);

                if (is_resource($process)) {
                    $stdout = stream_get_contents($pipes[1]);
                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);

                    $returnCode = proc_close($process);

                    if ($returnCode !== 0 || ! file_exists($imagePath)) {
                        Log::error('ImageMagick convert command failed', [
                            'command' => $command,
                            'return_code' => $returnCode,
                            'stderr' => $stderr,
                            'stdout' => $stdout,
                        ]);
                        return null;
                    }

                    Log::info('PDF cover extracted with ImageMagick convert', [
                        'pdf' => basename($pdfPath),
                        'image' => $imagePath,
                    ]);

                    return $imagePath;
                }

                Log::error('Failed to open ImageMagick process');
                return null;
            } else {
                // Unix/Linux systems
                $command = sprintf(
                    '%s -density 150 -quality 90 %s[0] %s',
                    $this->imagemagickCommand,
                    escapeshellarg($pdfPath),
                    escapeshellarg($imagePath)
                );

                exec($command, $output, $returnCode);

                if ($returnCode !== 0 || ! file_exists($imagePath)) {
                    Log::error('ImageMagick convert command failed', [
                        'command' => $command,
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
            }
        } catch (\Exception $e) {
            Log::error('ImageMagick PDF extraction failed', [
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
