<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;
use ZipArchive;

class DOCXMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from DOCX file.
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        if (! $this->fileExists($filePath)) {
            $this->logExtraction('error', 'DOCX file not found', ['file' => $filePath]);

            return $metadata;
        }

        try {
            $zip = new ZipArchive;
            if ($zip->open($filePath) !== true) {
                throw new \Exception('Failed to open DOCX file as ZIP');
            }

            // Extract from docProps/core.xml
            $metadata = $this->extractFromCoreProperties($zip);

            // Extract from document.xml if title not found
            if (! $metadata->getTitle()) {
                $metadata = $this->extractFromDocumentBody($zip, $metadata);
            }

            $zip->close();

            $this->logExtraction('info', 'DOCX extraction completed', [
                'file' => $filePath,
                'has_title' => (bool) $metadata->getTitle(),
                'has_authors' => ! empty($metadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'DOCX extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract metadata from docProps/core.xml.
     */
    private function extractFromCoreProperties(ZipArchive $zip): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        try {
            $coreContent = $zip->getFromName('docProps/core.xml');
            if (! $coreContent) {
                return $metadata;
            }

            $coreContent = $this->normalizeEncoding($coreContent);
            $xml = new \SimpleXMLElement($coreContent);

            // Register namespaces
            $namespaces = $xml->getNamespaces(true);
            $dcNs = $namespaces['dc'] ?? 'http://purl.org/dc/elements/1.1/';

            // Extract title
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'title') {
                    $title = $this->cleanText((string) $element);
                    if ($title) {
                        $metadata->setTitle($title, 0.85);
                    }
                }
            }

            // Extract creator/author
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'creator') {
                    $author = $this->cleanText((string) $element);
                    if ($author) {
                        $metadata->addAuthor($author, 0.85);
                    }
                }
            }

            // Extract lastModifiedBy as secondary author info
            foreach ($xml->children() as $element) {
                if ($element->getName() === 'lastModifiedBy') {
                    $modifier = $this->cleanText((string) $element);
                    if ($modifier && ! empty($metadata->getAuthors())) {
                        // Only add if we don't have author already
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logExtraction('debug', 'Failed to parse docProps/core.xml', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract metadata from document body (document.xml).
     */
    private function extractFromDocumentBody(ZipArchive $zip, ExtractedMetadata $metadata): ExtractedMetadata
    {
        try {
            $docContent = $zip->getFromName('word/document.xml');
            if (! $docContent) {
                return $metadata;
            }

            $docContent = $this->normalizeEncoding($docContent);
            $xml = new \SimpleXMLElement($docContent);

            // Extract text from first paragraph (often a title)
            $namespaces = $xml->getNamespaces(true);
            $wNs = $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

            // Get first few paragraphs
            $paragraphs = [];
            foreach ($xml->children($wNs) as $element) {
                if ($element->getName() === 'p') {
                    $text = $this->extractTextFromParagraph($element, $namespaces);
                    if ($text) {
                        $paragraphs[] = $text;
                    }
                }
            }

            // Use first paragraph as title if it's short and looks like a title
            if (! empty($paragraphs)) {
                $firstPara = $paragraphs[0];
                if (strlen($firstPara) < 200) {
                    $metadata->setTitle($firstPara, 0.5);
                }
            }
        } catch (\Exception $e) {
            $this->logExtraction('debug', 'Failed to parse document.xml', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract text from a paragraph element.
     */
    private function extractTextFromParagraph(\SimpleXMLElement $paragraph, array $namespaces): ?string
    {
        $text = '';
        $wNs = $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        foreach ($paragraph->children($wNs) as $element) {
            if ($element->getName() === 'r') {
                foreach ($element->children($wNs) as $child) {
                    if ($child->getName() === 't') {
                        $text .= (string) $child;
                    }
                }
            }
        }

        return $this->cleanText($text);
    }
}
