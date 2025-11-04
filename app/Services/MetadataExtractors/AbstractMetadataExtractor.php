<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

use Illuminate\Support\Facades\Log;

abstract class AbstractMetadataExtractor implements MetadataExtractorInterface
{
    /**
     * ISBN-10 and ISBN-13 pattern regex.
     */
    protected const ISBN_PATTERN = '/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i';

    /**
     * DOI pattern regex (10.xxxx/xxxx format).
     */
    protected const DOI_PATTERN = '/10\.\d{4,}\/\S+/i';

    /**
     * Extract ISBN from text.
     *
     * @param string $text
     * @return string|null ISBN without hyphens/spaces
     */
    protected function extractIsbn(string $text): ?string
    {
        if (preg_match(self::ISBN_PATTERN, $text, $matches)) {
            // Remove hyphens and spaces
            $isbn = preg_replace('/[\s-]/', '', $matches[0]);
            return !empty($isbn) ? $isbn : null;
        }
        return null;
    }

    /**
     * Extract DOI from text.
     *
     * @param string $text
     * @return string|null
     */
    protected function extractDoi(string $text): ?string
    {
        if (preg_match(self::DOI_PATTERN, $text, $matches)) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Extract year from text using various patterns.
     *
     * @param string $text
     * @return int|null Valid year between 1000 and current year
     */
    protected function extractYear(string $text): ?int
    {
        $currentYear = date('Y');

        // Match 4-digit numbers that look like years
        if (preg_match('/\b([12]\d{3})\b/', $text, $matches)) {
            $year = (int) $matches[1];
            if ($year >= 1000 && $year <= $currentYear) {
                return $year;
            }
        }

        return null;
    }

    /**
     * Normalize encoding to UTF-8.
     *
     * @param string $text
     * @return string
     */
    protected function normalizeEncoding(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $encoding = mb_detect_encoding($text, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'ASCII'], true);
            if ($encoding !== false) {
                $text = mb_convert_encoding($text, 'UTF-8', $encoding);
            } else {
                // Fallback to Windows-1251 (common for Russian texts) if detection fails
                $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1251');
            }
        }
        return $text;
    }

    /**
     * Detect language from text (simple detection).
     *
     * @param string $text
     * @return string Language code (en, ru, he, etc.)
     */
    protected function detectLanguage(string $text): string
    {
        // Simple detection: check for specific character ranges
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            return 'ru'; // Cyrillic
        }
        if (preg_match('/[\x{0590}-\x{05FF}]/u', $text)) {
            return 'he'; // Hebrew
        }
        if (preg_match('/[\x{4E00}-\x{9FFF}]/u', $text)) {
            return 'zh'; // Chinese
        }

        return 'en'; // Default to English
    }

    /**
     * Log extraction with context.
     *
     * @param string $level Log level (info, warning, error)
     * @param string $message
     * @param array $context
     */
    protected function logExtraction(string $level, string $message, array $context = []): void
    {
        Log::channel('folder_scan')->{$level}($message, $context);
    }

    /**
     * Trim and clean text.
     *
     * @param string|null $text
     * @return string|null
     */
    protected function cleanText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = trim($text);
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return !empty($text) ? $text : null;
    }

    /**
     * Split comma-separated authors safely.
     *
     * @param string $authorsText
     * @return array<string>
     */
    protected function parseAuthors(string $authorsText): array
    {
        $authors = array_map(
            fn ($author) => $this->cleanText($author),
            preg_split('/[,;]/', $authorsText)
        );

        // Filter out empty/null values
        return array_filter($authors, fn ($author) => $author !== null && !empty($author));
    }

    /**
     * Check if file exists and is readable.
     *
     * @param string $filePath
     * @return bool
     */
    protected function fileExists(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Get file size in bytes.
     *
     * @param string $filePath
     * @return int|null
     */
    protected function getFileSize(string $filePath): ?int
    {
        if (!$this->fileExists($filePath)) {
            return null;
        }

        $size = filesize($filePath);
        return $size !== false ? $size : null;
    }
}
