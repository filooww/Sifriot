<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;

class DJVUMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from DJVU file.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        if (!$this->fileExists($filePath)) {
            $this->logExtraction('error', 'DJVU file not found', ['file' => $filePath]);
            return $metadata;
        }

        try {
            // Try native DJVU library if available
            $metadata = $this->extractFromDjvuLibrary($filePath);

            // If extraction failed or empty, try filename fallback
            if ($metadata->isEmpty()) {
                $metadata = $this->extractFromFilename($filePath);
            }

            // Try OCR extraction if enabled and metadata is still minimal
            if ($metadata->isEmpty() && config('library.extraction.djvu_enable_ocr', false)) {
                $metadata = $this->extractViaOCR($filePath);
            }

            $this->logExtraction('info', 'DJVU extraction completed', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'used_ocr' => false,
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'DJVU extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            $metadata = $this->extractFromFilename($filePath);
        }

        return $metadata;
    }

    /**
     * Try to extract metadata using native DJVU library.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    private function extractFromDjvuLibrary(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        // Check if djvulibre-php or similar is available
        // For now, this is a placeholder as no standard PHP DJVU library exists
        $this->logExtraction('debug', 'DJVU native library not available, using fallback', []);

        return $metadata;
    }

    /**
     * Extract metadata from filename (primary fallback).
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    private function extractFromFilename(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $filename = $this->cleanText($filename);

        if ($filename) {
            $metadata->setTitle($filename, 0.3);

            // Try to extract author from "Title - Author" pattern
            if (str_contains($filename, '-')) {
                $parts = explode('-', $filename, 2);
                if (count($parts) === 2) {
                    $author = $this->cleanText($parts[1]);
                    if ($author && strlen($author) < 100) {
                        $metadata->addAuthor($author, 0.2);
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Extract metadata via OCR (optional, expensive).
     *
     * This is a placeholder for OCR integration (e.g., Tesseract).
     * Requires external OCR service and is disabled by default.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    private function extractViaOCR(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        $this->logExtraction('warning', 'DJVU OCR extraction requested but not implemented', [
            'file' => $filePath,
            'note' => 'Requires Tesseract or similar OCR library',
        ]);

        // Placeholder for future OCR implementation
        // Example: use external OCR service or Tesseract binding

        return $metadata;
    }
}
