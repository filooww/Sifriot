<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;

class FB2MetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from FB2 (FictionBook) file.
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        if (! $this->fileExists($filePath)) {
            $this->logExtraction('error', 'FB2 file not found', ['file' => $filePath]);

            return $metadata;
        }

        try {
            $content = file_get_contents($filePath);
            if (! $content) {
                throw new \Exception('Failed to read FB2 file');
            }

            $content = $this->normalizeEncoding($content);
            $xml = new \SimpleXMLElement($content);

            $metadata = $this->parseDescription($xml);

            $this->logExtraction('info', 'FB2 extraction completed', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'author_count' => count($metadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'FB2 extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            // Fallback to filename
            $metadata = $this->extractFromFilename($filePath);
        }

        return $metadata;
    }

    /**
     * Parse description element from FB2 XML.
     */
    private function parseDescription(\SimpleXMLElement $xml): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        // Find description element
        foreach ($xml->children() as $element) {
            if ($element->getName() === 'description') {
                $metadata = $this->extractFromDescription($element);
                break;
            }
        }

        return $metadata;
    }

    /**
     * Extract metadata from description element.
     */
    private function extractFromDescription(\SimpleXMLElement $description): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        // Find title-info section
        foreach ($description->children() as $section) {
            if ($section->getName() === 'title-info') {
                $metadata = $this->extractFromTitleInfo($section);
                break;
            }
        }

        return $metadata;
    }

    /**
     * Extract metadata from title-info section.
     */
    private function extractFromTitleInfo(\SimpleXMLElement $titleInfo): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        foreach ($titleInfo->children() as $element) {
            $name = $element->getName();

            // Extract book title
            if ($name === 'book-title') {
                $title = $this->cleanText((string) $element);
                if ($title) {
                    $metadata->setTitle($title, 0.95);
                }
            }

            // Extract authors
            if ($name === 'author') {
                $author = $this->extractAuthorName($element);
                if ($author) {
                    $metadata->addAuthor($author, 0.95);
                }
            }

            // Extract publisher
            if ($name === 'publisher') {
                $publisher = $this->cleanText((string) $element);
                if ($publisher) {
                    $metadata->setPublisher($publisher, 0.95);
                }
            }

            // Extract date (publication year)
            if ($name === 'date') {
                $dateStr = (string) $element;
                if ($dateStr) {
                    $year = $this->extractYear($dateStr);
                    if ($year) {
                        $metadata->setPublicationYear($year, 0.9);
                    }
                }
            }

            // Extract year (alternative)
            if ($name === 'year') {
                $year = (int) $element;
                if ($year > 1000 && $year <= date('Y')) {
                    $metadata->setPublicationYear($year, 0.95);
                }
            }

            // Extract genres
            if ($name === 'genre') {
                $genre = $this->cleanText((string) $element);
                if ($genre) {
                    $metadata->addGenre($genre, 0.9);
                }
            }
        }

        return $metadata;
    }

    /**
     * Extract author name from author element.
     */
    private function extractAuthorName(\SimpleXMLElement $authorElement): ?string
    {
        $parts = [];

        foreach ($authorElement->children() as $element) {
            $name = $element->getName();

            if ($name === 'first-name') {
                $parts[] = $this->cleanText((string) $element);
            } elseif ($name === 'middle-name') {
                $parts[] = $this->cleanText((string) $element);
            } elseif ($name === 'last-name') {
                $parts[] = $this->cleanText((string) $element);
            }
        }

        if (empty($parts)) {
            return null;
        }

        $author = implode(' ', array_filter($parts));

        return $this->cleanText($author);
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
