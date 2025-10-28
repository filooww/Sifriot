<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;

class PDFMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from PDF file.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        if (!$this->fileExists($filePath)) {
            $this->logExtraction('error', 'PDF file not found', ['file' => $filePath]);
            return $metadata;
        }

        try {
            // Attempt to use smalot/pdfparser if available
            if (class_exists('\Smalot\PdfParser\Parser')) {
                $metadata = $this->extractWithParser($filePath);
            } else {
                // Fallback: Extract from filename
                $metadata = $this->extractFromFilename($filePath);
            }

            // Try to extract ISBN/DOI from PDF text
            $text = $this->extractTextFromPdf($filePath);
            if ($text) {
                if (!$metadata->getIsbn()) {
                    $isbn = $this->extractIsbn($text);
                    if ($isbn) {
                        $metadata->setIsbn($isbn, 0.6);
                    }
                }

                if (!$metadata->getDoi()) {
                    $doi = $this->extractDoi($text);
                    if ($doi) {
                        $metadata->setDoi($doi, 0.6);
                    }
                }

                if (!$metadata->getPublicationYear()) {
                    $year = $this->extractYear($text);
                    if ($year) {
                        $metadata->setPublicationYear($year, 0.5);
                    }
                }
            }

            $this->logExtraction('info', 'PDF extraction completed', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'has_authors' => !empty($metadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'PDF extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract metadata using smalot/pdfparser library.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    private function extractWithParser(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);

            $details = $pdf->getDetails();

            // Extract title from metadata
            if (isset($details['Title'])) {
                $title = $this->cleanText($details['Title'][0] ?? '');
                if ($title) {
                    $metadata->setTitle($title, 0.9);
                }
            }

            // Extract author from metadata
            if (isset($details['Author'])) {
                $author = $this->cleanText($details['Author'][0] ?? '');
                if ($author) {
                    foreach ($this->parseAuthors($author) as $parsedAuthor) {
                        $metadata->addAuthor($parsedAuthor, 0.9);
                    }
                }
            }

            // Extract creation date
            if (isset($details['CreationDate'])) {
                $date = $details['CreationDate'][0] ?? null;
                if ($date) {
                    $year = $this->extractYear($date);
                    if ($year) {
                        $metadata->setPublicationYear($year, 0.7);
                    }
                }
            }

            // Extract subject (might contain publisher info)
            if (isset($details['Subject'])) {
                $subject = $this->cleanText($details['Subject'][0] ?? '');
                if ($subject && !$metadata->getPublisher()) {
                    $metadata->setPublisher($subject, 0.4);
                }
            }
        } catch (\Exception $e) {
            $this->logExtraction('warning', 'Failed to parse PDF with smalot/pdfparser', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract text content from PDF file.
     *
     * @param string $filePath
     * @return string|null
     */
    private function extractTextFromPdf(string $filePath): ?string
    {
        try {
            if (!class_exists('\Smalot\PdfParser\Parser')) {
                return null;
            }

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            return !empty($text) ? substr($text, 0, 5000) : null; // Limit to first 5000 chars
        } catch (\Exception $e) {
            $this->logExtraction('debug', 'Failed to extract text from PDF', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Fallback: Extract metadata from filename.
     *
     * @param string $filePath
     * @return ExtractedMetadata
     */
    private function extractFromFilename(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata();

        // Get filename without extension
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $filename = $this->cleanText($filename);

        if ($filename) {
            // Use filename as title suggestion
            $metadata->setTitle($filename, 0.3);

            // Try to extract author from filename (common pattern: "Title - Author")
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
}
