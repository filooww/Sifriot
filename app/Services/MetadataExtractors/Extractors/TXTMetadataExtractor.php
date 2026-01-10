<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;

class TXTMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Maximum characters to read from file for pattern matching.
     */
    private const MAX_CHARS_TO_READ = 500;

    /**
     * Extract metadata from TXT file.
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        if (! $this->fileExists($filePath)) {
            $this->logExtraction('error', 'TXT file not found', ['file' => $filePath]);

            return $metadata;
        }

        try {
            $content = $this->readFileContent($filePath);

            if (! $content) {
                $metadata = $this->extractFromFilename($filePath);
            } else {
                $metadata = $this->parseContent($content, $filePath);
            }

            $this->logExtraction('info', 'TXT extraction completed', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'has_authors' => ! empty($metadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'TXT extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            $metadata = $this->extractFromFilename($filePath);
        }

        return $metadata;
    }

    /**
     * Read file content safely with encoding detection.
     */
    private function readFileContent(string $filePath): ?string
    {
        try {
            $content = file_get_contents($filePath, false, null, 0, self::MAX_CHARS_TO_READ);
            if ($content === false) {
                return null;
            }

            // Normalize encoding
            $content = $this->normalizeEncoding($content);

            return $content;
        } catch (\Exception $e) {
            $this->logExtraction('debug', 'Failed to read TXT file', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Parse TXT content for metadata patterns.
     */
    private function parseContent(string $content, string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        // Split into lines
        $lines = preg_split('/[\r\n]+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($lines)) {
            return $this->extractFromFilename($filePath);
        }

        // Try to extract title from first line
        $firstLine = $this->cleanText($lines[0]);
        if ($firstLine && strlen($firstLine) < 200) {
            $metadata->setTitle($firstLine, 0.3);
        }

        // Search first few lines for author patterns
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            $line = $lines[$i];

            // Pattern: "by Author Name"
            if (preg_match('/\bby\s+([A-Z][^,\n]+)/i', $line, $matches)) {
                $author = $this->cleanText($matches[1]);
                if ($author && strlen($author) < 100) {
                    $metadata->addAuthor($author, 0.35);
                }
            }

            // Pattern: "Author: Name"
            if (preg_match('/author\s*:\s*([^,\n]+)/i', $line, $matches)) {
                $author = $this->cleanText($matches[1]);
                if ($author && strlen($author) < 100) {
                    $metadata->addAuthor($author, 0.35);
                }
            }

            // Pattern: "Title: ..."
            if (! $metadata->getTitle() && preg_match('/title\s*:\s*([^,\n]+)/i', $line, $matches)) {
                $title = $this->cleanText($matches[1]);
                if ($title) {
                    $metadata->setTitle($title, 0.35);
                }
            }
        }

        // If no title found, use filename
        if (! $metadata->getTitle()) {
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            $filename = $this->cleanText($filename);
            if ($filename) {
                $metadata->setTitle($filename, 0.2);
            }
        }

        return $metadata;
    }

    /**
     * Extract metadata from filename (fallback).
     */
    private function extractFromFilename(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $filename = $this->cleanText($filename);

        if ($filename) {
            $metadata->setTitle($filename, 0.2);

            // Try "Title - Author" pattern
            if (str_contains($filename, '-')) {
                $parts = explode('-', $filename, 2);
                if (count($parts) === 2) {
                    $author = $this->cleanText($parts[1]);
                    if ($author && strlen($author) < 100) {
                        $metadata->addAuthor($author, 0.15);
                    }
                }
            }
        }

        return $metadata;
    }
}
