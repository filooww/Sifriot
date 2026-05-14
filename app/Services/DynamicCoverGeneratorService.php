
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

            // Create gradient background based on genre
            $backgroundColor = $this->getGenreColor($genre);

            $image = imagecreatetruecolor($width, $height);

            // Create gradient background
            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / $height;
                $color = $this->interpolateColor($backgroundColor['start'], $backgroundColor['end'], $ratio);
                imageline($image, 0, $y, $width, $y, $color);
            }

            // Add text elements
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $shadowColor = imagecolorallocate($image, 0, 0, 0);

            // Add title
            $fontSize = $this->calculateFontSize($title, $width - 80, 40);
            $titleY = $height * 0.3;

            // Draw text shadow
            $wrappedTitle = wordwrap($title, 25, "\n", true);
            $lines = explode("\n", $wrappedTitle);

            foreach ($lines as $index => $line) {
                $lineY = $titleY + ($index * 60);

                // Shadow
                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $width / 2 + 3,
                    $lineY + 3,
                    $shadowColor,
                    $this->getFontPath(),
                    $line
                );

                // Main text
                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $width / 2,
                    $lineY,
                    $textColor,
                    $this->getFontPath(),
                    $line
                );
            }

            // Add author
            if (!empty($author)) {
                $authorY = $titleY + (count($lines) * 60) + 30;
                imagettftext(
                    $image,
                    24,
                    0,
                    $width / 2 + 2,
                    $authorY + 2,
                    $shadowColor,
                    $this->getFontPath(),
                    "by " . $author
                );
                imagettftext(
                    $image,
                    24,
                    0,
                    $width / 2,
                    $authorY,
                    $textColor,
                    $this->getFontPath(),
                    "by " . $author
                );
            }

            // Add format badge
            $badgeY = $height - 100;
            imagettftext(
                $image,
                20,
                0,
                $width / 2,
                $badgeY,
                $textColor,
                $this->getFontPath(),
                strtoupper($format)
            );

            // Save image
            $tempPath = storage_path('app/temp/' . uniqid('cover_', true) . '.png');
            imagepng($image, $tempPath, 90);
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

        // Try progressively smaller sizes until it fits
        while ($size > 12) {
            $lines = explode("\n", wordwrap($text, 25, "\n", true));
            $maxLineWidth = 0;

            foreach ($lines as $line) {
                $dimensions = imagettfbbox($size, 0, $fontPath, $line);
                $lineWidth = abs($dimensions[2] - $dimensions[0]);
                $maxLineWidth = max($maxLineWidth, $lineWidth);
            }

            if ($maxLineWidth <= $maxWidth) {
                break;
            }

            $size -= 2;
        }

        return $size;
    }

    /**
     * Get path to a TTF font file.
     */
    private function getFontPath(): string
    {
        // Try common font locations
        $fonts = [
            __DIR__ . '/../../resources/fonts/OpenSans-Regular.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        // Fallback - this will fail but provides a clear error
        return 'arial.ttf';
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
            $iconX = $width / 2 - 60;
            $iconY = $height / 2 - 100;

            // Draw book shape
            imagefilledrectangle($image, $iconX, $iconY, $iconX + 120, $iconY + 160, $iconColor);

            // Add format text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            imagettftext(
                $image,
                24,
                0,
                $width / 2,
                $iconY + 200,
                $textColor,
                $this->getFontPath(),
                strtoupper($format)
            );

            // Save image
            $tempPath = storage_path('app/temp/' . uniqid('icon_cover_', true) . '.png');
            imagepng($image, $tempPath, 90);
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
