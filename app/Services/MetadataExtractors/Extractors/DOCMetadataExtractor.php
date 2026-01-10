<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;

class DOCMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from DOC file (legacy format).
     *
     * Note: DOC format support is limited. This extractor attempts to extract
     * from metadata but falls back to filename and text patterns.
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        if (! $this->fileExists($filePath)) {
            $this->logExtraction('error', 'DOC file not found', ['file' => $filePath]);

            return $metadata;
        }

        try {
            // Try to use PHPOffice if available
            if (class_exists('\PhpOffice\PhpWord\PhpWord')) {
                $metadata = $this->extractWithPhpOffice($filePath);
            }

            // Always try filename as fallback
            if ($metadata->isEmpty()) {
                $metadata = $this->extractFromFilename($filePath);
            }

            $this->logExtraction('warning', 'DOC extraction completed (legacy format, partial support)', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'confidence' => 'medium',
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'DOC extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            // Fallback to filename
            $metadata = $this->extractFromFilename($filePath);
        }

        return $metadata;
    }

    /**
     * Extract metadata using PHPOffice/PHPWord (if available).
     */
    private function extractWithPhpOffice(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);

            // PHPWord has limited support for DOC format properties
            // Try to get document properties if available
            $properties = $phpWord->getDocInfo();

            if ($properties) {
                $title = $properties->getTitle();
                if ($title) {
                    $metadata->setTitle($this->cleanText($title), 0.5);
                }

                $creator = $properties->getCreator();
                if ($creator) {
                    $metadata->addAuthor($this->cleanText($creator), 0.5);
                }

                $subject = $properties->getSubject();
                if ($subject) {
                    $metadata->setPublisher($this->cleanText($subject), 0.3);
                }
            }
        } catch (\Exception $e) {
            $this->logExtraction('debug', 'PHPOffice failed for DOC file', [
                'error' => $e->getMessage(),
            ]);
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

            // Try to extract author from common pattern: "Title - Author"
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
