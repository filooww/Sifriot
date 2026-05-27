<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\ExtractedMetadata;

class RTFMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from RTF file.
     */
    public function extract(string $filePath, ?int $contentTypeId = null): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                return $metadata;
            }

            // Extract title from RTF content
            $title = $this->extractTitle($content);
            if ($title) {
                $metadata->setTitle($title);
            }

            // Extract author from RTF content
            $author = $this->extractAuthor($content);
            if ($author) {
                $metadata->setAuthors([$author]);
            }

            // Extract publication year if present
            $year = $this->extractYear($content);
            if ($year) {
                $metadata->setPublicationYear((int) $year);
            }

            // Set confidence scores (RTF metadata extraction is less reliable)
            $metadata->setConfidence('title', 0.6);
            $metadata->setConfidence('authors', 0.5);
            $metadata->setConfidence('publication_year', 0.4);

        } catch (\Exception $e) {
            // Log error but return empty metadata
            $this->log('warning', 'RTF metadata extraction failed', [
                'file' => basename($filePath),
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract title from RTF content.
     */
    private function extractTitle(string $content): ?string
    {
        // RTF files may contain title in {\title ...} or info section
        if (preg_match('/\{\\\\title\s+([^\}]+)\}/', $content, $matches)) {
            return trim($matches[1]);
        }

        // Try to find first significant text that might be a title
        $plainText = $this->stripRtfFormatting($content);
        $lines = explode("\n", $plainText);

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines and very short lines
            if (strlen($line) > 3 && strlen($line) < 200) {
                // Skip lines that are likely headers/footers
                if (!preg_match('/^(page|chapter|section|\d+)$/i', $line)) {
                    return $line;
                }
            }
        }

        return null;
    }

    /**
     * Extract author from RTF content.
     */
    private function extractAuthor(string $content): ?string
    {
        // Check for author in RTF metadata
        if (preg_match('/\{\\\\author\s+([^\}]+)\}/', $content, $matches)) {
            return trim($matches[1]);
        }

        // Look for common author patterns in text
        $plainText = $this->stripRtfFormatting($content);
        $patterns = [
            '/(?:by|author|written by)[:\s]+([A-Z][a-zA-Z\s]+)/i',
            '/^([A-Z][a-zA-Z\s]+)$/m',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $plainText, $matches)) {
                $author = trim($matches[1]);
                if (strlen($author) > 3 && strlen($author) < 100) {
                    return $author;
                }
            }
        }

        return null;
    }

    /**
     * Extract year from RTF content.
     */
    private function extractYear(string $content): ?string
    {
        $plainText = $this->stripRtfFormatting($content);

        // Look for 4-digit years between 1900-2099
        if (preg_match('/\b(19|20)\d{2}\b/', $plainText, $matches)) {
            $year = $matches[0];
            if ($year >= 1900 && $year <= 2099) {
                return $year;
            }
        }

        return null;
    }

    /**
     * Strip RTF formatting codes and extract plain text.
     */
    private function stripRtfFormatting(string $rtf): string
    {
        // Remove RTF header
        $rtf = preg_replace('/\{\\\\rtf1[^\{]*/', '', $rtf);

        // Remove all control words (backslash sequences)
        $rtf = preg_replace('/\\\\[a-z0-9]+(\-?[0-9]+)?[ ]?/i', ' ', $rtf);

        // Remove hex characters
        $rtf = preg_replace('/\\\\\'[0-9a-fA-F]{2}/', ' ', $rtf);

        // Remove remaining special characters
        $rtf = preg_replace('/[{}\\\\]/', ' ', $rtf);

        // Remove Unicode escapes
        $rtf = preg_replace('/\\\\u(\d+)(\?.?)/', ' ', $rtf);

        // Normalize whitespace
        $rtf = preg_replace('/\s+/', ' ', $rtf);

        return trim($rtf);
    }

    /**
     * Get supported MIME types.
     *
     * @return string[]
     */
    public function getSupportedMimeTypes(): array
    {
        return [
            'application/rtf',
            'text/rtf',
        ];
    }

    /**
     * Get supported file extensions.
     *
     * @return string[]
     */
    public function getSupportedExtensions(): array
    {
        return ['rtf'];
    }
}