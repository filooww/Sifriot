<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DynamicCoverGeneratorService
{
    /**
     * Generate a placeholder cover image based on metadata.
     *
     * @param  string  $title
     * @param  string  $author
     * @param  string  $genre
     * @param  string  $format
     * @return string|null  Path to generated image or null on failure
     */
    public function generatePlaceholderCover(
        string $title,
        string $author = '',
        string $genre = '',
        string $format = 'book'
    ): ?string {
        try {
            // Create image with GD or Imagick
            $width = 600;
            $height = 800;

            // Create gradient background based on file type (prioritize over genre)
            $backgroundColor = $this->getFileTypeColor($format, $genre);

            $image = imagecreatetruecolor($width, $height);

            // Create gradient background with better quality
            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / $height;
                $color = $this->interpolateColor($backgroundColor['start'], $backgroundColor['end'], $ratio);
                imageline($image, 0, $y, $width, $y, $color);
            }

            // Add subtle texture overlay (reduced to minimize scratches)
            $this->addTextureOverlay($image, $width, $height);

            // Add text elements with improved typography
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 80);
            $accentColor = imagecolorallocatealpha($image, 255, 255, 255, 50);

            // Add decorative border
            $borderThickness = 8;
            $borderColor = imagecolorallocatealpha($image, 255, 255, 255, 30);
            imagerectangle($image, $borderThickness, $borderThickness, $width - $borderThickness, $height - $borderThickness, $borderColor);

            // Get font path and check if TTF is available
            $fontPath = $this->getFontPath();
            $useTtf = !empty($fontPath) && file_exists($fontPath);

            // Add title with improved typography
            $fontSize = $this->calculateFontSize($title, $width - 100, 42);
            $titleY = (int)($height * 0.28);

            // Draw text shadow with better blur effect
            $wrappedTitle = wordwrap($title, 22, "\n", true);
            $lines = explode("\n", $wrappedTitle);

            foreach ($lines as $index => $line) {
                $lineY = $titleY + ($index * 65);

                if ($useTtf) {
                    // Multiple shadow layers for better blur effect
                    for ($blur = 4; $blur >= 1; $blur--) {
                        $this->drawCenteredTtfText(
                            $image,
                            $line,
                            (int)($width / 2) + $blur,
                            $lineY + $blur,
                            $fontSize,
                            $shadowColor,
                            $fontPath
                        );
                    }

                    // Main text (centered)
                    $this->drawCenteredTtfText(
                        $image,
                        $line,
                        (int)($width / 2),
                        $lineY,
                        $fontSize,
                        $textColor,
                        $fontPath
                    );
                } else {
                    // Fallback to built-in font functions
                    $this->drawBuiltInText($image, $line, (int)($width / 2), $lineY, $textColor, $shadowColor, $fontSize);
                }
            }

            // Add decorative line under title
            if (count($lines) > 0) {
                $lineY = $titleY + (count($lines) * 65) + 15;
                $lineLength = min(300, strlen($lines[0]) * 8);
                imageline(
                    $image,
                    ($width - $lineLength) / 2,
                    $lineY,
                    ($width + $lineLength) / 2,
                    $lineY,
                    $accentColor
                );
            }

            // Add author with improved styling
            if (!empty($author)) {
                $authorY = $titleY + (count($lines) * 65) + 50; // Increased spacing from 40 to 50
                $authorText = "by " . $author;

                // Make author text more prominent with larger size
                $authorSize = 28; // Increased from 26

                // Add subtle accent line above author
                $authorLineY = $authorY - 15;
                $authorLineColor = imagecolorallocatealpha($image, 255, 255, 255, 40);
                $authorLineLength = min(200, strlen($authorText) * 10);
                imageline(
                    $image,
                    ($width - $authorLineLength) / 2,
                    $authorLineY,
                    ($width + $authorLineLength) / 2,
                    $authorLineY,
                    $authorLineColor
                );

                if ($useTtf) {
                    // Author shadow with more prominence
                    for ($blur = 3; $blur >= 1; $blur--) {
                        $this->drawCenteredTtfText(
                            $image,
                            $authorText,
                            (int)($width / 2) + $blur,
                            $authorY + $blur,
                            $authorSize,
                            $shadowColor,
                            $fontPath
                        );
                    }

                    // Main author text (centered) with slight opacity for elegant look
                    $authorTextColor = imagecolorallocatealpha($image, 255, 255, 255, 30); // Slightly transparent (0-127 scale)
                    $this->drawCenteredTtfText(
                        $image,
                        $authorText,
                        (int)($width / 2),
                        $authorY,
                        $authorSize,
                        $authorTextColor,
                        $fontPath
                    );
                } else {
                    // Fallback to built-in font
                    $this->drawBuiltInText($image, $authorText, (int)($width / 2), $authorY, $textColor, $shadowColor, $authorSize);
                }
            }

            // Add format badge with better styling
            $badgeY = $height - 80;
            $badgeText = strtoupper($format);
            $badgeSize = 22;

            if ($useTtf) {
                // Badge shadow
                for ($blur = 2; $blur >= 1; $blur--) {
                    $this->drawCenteredTtfText(
                        $image,
                        $badgeText,
                        (int)($width / 2) + $blur,
                        $badgeY + $blur,
                        $badgeSize,
                        $shadowColor,
                        $fontPath
                    );
                }

                // Main badge text (centered)
                $this->drawCenteredTtfText(
                    $image,
                    $badgeText,
                    (int)($width / 2),
                    $badgeY,
                    $badgeSize,
                    $textColor,
                    $fontPath
                );
            } else {
                // Fallback to built-in font
                $this->drawBuiltInText($image, $badgeText, (int)($width / 2), $badgeY, $textColor, $shadowColor, $badgeSize);
            }

            // Add small corner decorations
            $cornerSize = 30;
            $cornerColor = imagecolorallocatealpha($image, 255, 255, 255, 20);

            // Top-left corner
            imageline($image, 0, 0, $cornerSize, 0, $cornerColor);
            imageline($image, 0, 0, 0, $cornerSize, $cornerColor);

            // Top-right corner
            imageline($image, $width - $cornerSize, 0, $width, 0, $cornerColor);
            imageline($image, $width, 0, $width, $cornerSize, $cornerColor);

            // Bottom-left corner
            imageline($image, 0, $height - $cornerSize, 0, $height, $cornerColor);
            imageline($image, 0, $height, $cornerSize, $height, $cornerColor);

            // Bottom-right corner
            imageline($image, $width - $cornerSize, $height, $width, $height, $cornerColor);
            imageline($image, $width, $height - $cornerSize, $width, $height, $cornerColor);

            // Save image
            $tempPath = storage_path('app/temp/' . uniqid('cover_', true) . '.png');
            imagepng($image, $tempPath, 6); // PNG compression level 0-9 (6 = good balance)
            imagedestroy($image);

            Log::info('Dynamic cover generated', [
                'title' => $title,
                'format' => $format,
                'path' => $tempPath,
            ]);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate dynamic cover', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get color scheme based on genre.
     */
    private function getGenreColor(string $genre): array
    {
        $genreLower = strtolower($genre);

        $colorSchemes = [
            'fiction' => ['start' => [70, 130, 180], 'end' => [25, 25, 112]], // Blue
            'fantasy' => ['start' => [138, 43, 226], 'end' => [75, 0, 130]], // Purple
            'romance' => ['start' => [255, 182, 193], 'end' => [219, 112, 147]], // Pink
            'horror' => ['start' => [139, 69, 19], 'end' => [0, 0, 0]], // Dark red to black
            'scifi' => ['start' => [0, 191, 255], 'end' => [0, 0, 139]], // Cyan to dark blue
            'mystery' => ['start' => [105, 105, 105], 'end' => [0, 0, 0]], // Gray to black
            'history' => ['start' => [210, 180, 140], 'end' => [139, 69, 19]], // Tan to brown
            'biography' => ['start' => [173, 216, 230], 'end' => [0, 0, 128]], // Light blue to navy
        ];

        // Find matching genre
        foreach ($colorSchemes as $key => $colors) {
            if (str_contains($genreLower, $key)) {
                return $colors;
            }
        }

        // Default gradient
        return ['start' => [72, 61, 139], 'end' => [25, 25, 112]]; // Default purple
    }

    /**
     * Get color scheme based on file type with genre fallback.
     */
    private function getFileTypeColor(string $format, string $genre = ''): array
    {
        $formatLower = strtolower($format);

        $fileTypeColors = [
            'pdf' => ['start' => [30, 58, 138], 'end' => [30, 64, 175]], // Deep blue
            'epub' => ['start' => [107, 33, 168], 'end' => [124, 58, 237]], // Purple
            'mobi' => ['start' => [107, 33, 168], 'end' => [124, 58, 237]], // Purple (same as EPUB)
            'azw' => ['start' => [107, 33, 168], 'end' => [124, 58, 237]], // Purple (same as EPUB)
            'docx' => ['start' => [234, 88, 12], 'end' => [249, 115, 22]], // Orange
            'doc' => ['start' => [234, 88, 12], 'end' => [249, 115, 22]], // Orange
            'txt' => ['start' => [75, 85, 99], 'end' => [107, 114, 128]], // Gray
            'djvu' => ['start' => [6, 182, 212], 'end' => [8, 145, 178]], // Cyan
            'fb2' => ['start' => [34, 197, 94], 'end' => [22, 163, 74]], // Green
            'rtf' => ['start' => [234, 88, 12], 'end' => [249, 115, 22]], // Orange (same as DOC)
            'odt' => ['start' => [37, 99, 235], 'end' => [59, 130, 246]], // Blue
        ];

        // Return file-type color if found
        if (isset($fileTypeColors[$formatLower])) {
            return $fileTypeColors[$formatLower];
        }

        // Fallback to genre-based colors
        if (!empty($genre)) {
            return $this->getGenreColor($genre);
        }

        // Default gradient
        return ['start' => [15, 118, 110], 'end' => [20, 184, 166]]; // Teal
    }

    /**
     * Add subtle texture overlay to image (minimal to avoid scratches).
     */
    private function addTextureOverlay($image, int $width, int $height): void
    {
        // Very subtle noise - reduced significantly
        $textureColor = imagecolorallocatealpha($image, 255, 255, 255, 5);

        // Add minimal noise texture (reduced from 500 to 50 pixels)
        for ($i = 0; $i < 50; $i++) {
            $x = rand(0, $width);
            $y = rand(0, $height);
            imagesetpixel($image, $x, $y, $textureColor);
        }

        // Remove the horizontal lines that were causing scratches
        // They were creating visible "scratch" marks across the cover
    }

    /**
     * Interpolate between two colors.
     */
    private function interpolateColor(array $color1, array $color2, float $ratio): int
    {
        $r = (int) ($color1[0] + ($color2[0] - $color1[0]) * $ratio);
        $g = (int) ($color1[1] + ($color2[1] - $color1[1]) * $ratio);
        $b = (int) ($color1[2] + ($color2[2] - $color1[2]) * $ratio);

        return imagecolorallocate(
            imagecreatetruecolor(1, 1),
            min(255, max(0, $r)),
            min(255, max(0, $g)),
            min(255, max(0, $b))
        );
    }

    /**
     * Calculate font size to fit text within width.
     */
    private function calculateFontSize(string $text, int $maxWidth, int $maxSize = 40): int
    {
        $size = $maxSize;
        $fontPath = $this->getFontPath();

        // Check if TTF is available
        if (empty($fontPath) || !file_exists($fontPath)) {
            // For built-in fonts, just return a reasonable size
            return min(5, $maxSize); // Built-in fonts only go 1-5
        }

        // Try progressively smaller sizes until it fits
        while ($size > 12) {
            $lines = explode("\n", wordwrap($text, 25, "\n", true));
            $maxLineWidth = 0;

            foreach ($lines as $line) {
                $dimensions = @imagettfbbox($size, 0, $fontPath, $line);
                if ($dimensions !== false) {
                    $lineWidth = abs($dimensions[2] - $dimensions[0]);
                    $maxLineWidth = max($maxLineWidth, $lineWidth);
                } else {
                    // Font calculation failed, use fallback size
                    return 20;
                }
            }

            if ($maxLineWidth <= $maxWidth) {
                break;
            }

            $size -= 2;
        }

        return $size;
    }

    /**
     * Get path to a TTF font file with multiple fallback options and validation.
     */
    private function getFontPath(): string
    {
        // Try system fonts first (most reliable)
        $systemFonts = [
            'C:\\Windows\\Fonts\\arial.ttf', // Arial - Windows most reliable
            'C:\\Windows\\Fonts\\arialbd.ttf', // Arial Bold - Windows
            'C:\\Windows\\Fonts\\segoeui.ttf', // Segoe UI - Windows
            'C:\\Windows\\Fonts\\calibri.ttf', // Calibri - Windows
            'C:\\Windows\\Fonts\\tahoma.ttf', // Tahoma - Windows
            'C:\\Windows\\Fonts\\verdana.ttf', // Verdana - Windows
            '/System/Library/Fonts/Helvetica.ttc', // Helvetica - macOS
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', // DejaVu Sans - Linux
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf', // Liberation Sans - Linux
        ];

        // Try custom fonts if they exist
        $customFonts = [
            __DIR__ . '/../../resources/fonts/OpenSans-Regular.ttf',
            __DIR__ . '/../../resources/fonts/Roboto-Regular.ttf',
            __DIR__ . '/../../resources/fonts/Montserrat-Regular.ttf',
            __DIR__ . '/../../resources/fonts/inter.ttf',
            __DIR__ . '/../../resources/fonts/poppins.ttf',
        ];

        $allFonts = array_merge($systemFonts, $customFonts);

        foreach ($allFonts as $font) {
            if (file_exists($font) && is_readable($font)) {
                // Test if the font is actually valid by trying to get bounding box
                $testBox = @imagettfbbox(10, 0, $font, 'Test');
                if ($testBox !== false) {
                    Log::debug('Font loaded successfully', ['font' => $font]);
                    return $font;
                } else {
                    Log::debug('Font file exists but failed to load', ['font' => $font]);
                }
            }
        }

        // Last resort - try to find any working TTF font in system directories
        $fontDirectories = [
            'C:\\Windows\\Fonts\\',
            '/System/Library/Fonts/',
            '/usr/share/fonts/truetype/',
            '/usr/share/fonts/',
        ];

        foreach ($fontDirectories as $dir) {
            if (is_dir($dir) && is_readable($dir)) {
                $files = scandir($dir);
                foreach ($files as $file) {
                    if (str_ends_with(strtolower($file), '.ttf')) {
                        $fullPath = $dir . $file;
                        if (file_exists($fullPath) && is_readable($fullPath)) {
                            $testBox = @imagettfbbox(10, 0, $fullPath, 'Test');
                            if ($testBox !== false) {
                                Log::info('Found working fallback font', ['font' => $fullPath]);
                                return $fullPath;
                            }
                        }
                    }
                }
            }
        }

        // Absolute fallback - use built-in font functions
        Log::error('No suitable TTF font found for cover generation');
        return ''; // Empty string will trigger fallback to built-in fonts
    }

    /**
     * Draw centered TTF text with proper positioning.
     */
    private function drawCenteredTtfText(
        $image,
        string $text,
        int $centerX,
        int $y,
        int $size,
        int $color,
        string $fontPath,
        int $angle = 0
    ): void {
        // Get text bounding box to calculate proper centering
        $bbox = imagettfbbox($size, $angle, $fontPath, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);

        // Calculate x position to center the text and cast to int
        $x = (int)($centerX - ($textWidth / 2));

        imagettftext($image, $size, $angle, $x, $y, $color, $fontPath, $text);
    }

    /**
     * Draw text using built-in GD functions when TTF fonts are not available.
     */
    private function drawBuiltInText($image, string $text, int $x, int $y, int $color, ?int $shadowColor = null, int $fontSize = 20): void
    {
        // Calculate text width for centering
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textX = $x - ($textWidth / 2);

        // Draw shadow if provided
        if ($shadowColor !== null) {
            imagestring($image, $fontSize, $textX + 2, $y + 2, $text, $shadowColor);
        }

        // Draw main text
        imagestring($image, $fontSize, $textX, $y, $text, $color);
    }

    /**
     * Generate icon-based cover for formats without text.
     */
    public function generateIconCover(string $format, string $icon = '📖'): ?string
    {
        try {
            $width = 600;
            $height = 800;

            $image = imagecreatetruecolor($width, $height);

            // Create simple gradient background
            $startColor = imagecolorallocate($image, 72, 61, 139);
            $endColor = imagecolorallocate($image, 25, 25, 112);

            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / $height;
                $r = (int) (72 + (25 - 72) * $ratio);
                $g = (int) (61 + (25 - 61) * $ratio);
                $b = (int) (139 + (112 - 139) * $ratio);
                $color = imagecolorallocate($image, $r, $g, $b);
                imageline($image, 0, $y, $width, $y, $color);
            }

            // Add large icon (using emoji or simple shapes)
            $iconColor = imagecolorallocate($image, 255, 255, 255);

            // Draw simple book icon using GD shapes
            $iconX = (int)($width / 2) - 60;
            $iconY = (int)($height / 2) - 100;

            // Draw book shape
            imagefilledrectangle($image, $iconX, $iconY, $iconX + 120, $iconY + 160, $iconColor);

            // Add format text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $fontPath = $this->getFontPath();

            if (!empty($fontPath) && file_exists($fontPath)) {
                $this->drawCenteredTtfText(
                    $image,
                    strtoupper($format),
                    (int)($width / 2),
                    $iconY + 200,
                    24,
                    $textColor,
                    $fontPath
                );
            } else {
                // Fallback to built-in text
                $this->drawBuiltInText($image, strtoupper($format), (int)($width / 2), $iconY + 200, $textColor, null, 24);
            }

            // Save image
            $tempPath = storage_path('app/temp/' . uniqid('icon_cover_', true) . '.png');
            imagepng($image, $tempPath, 6); // PNG compression level 0-9 (6 = good balance)
            imagedestroy($image);

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate icon cover', [
                'format' => $format,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
