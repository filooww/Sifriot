<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors\Extractors;

use App\Services\MetadataExtractors\AbstractMetadataExtractor;
use App\Services\MetadataExtractors\ExtractedMetadata;
use ZipArchive;

class EPUBMetadataExtractor extends AbstractMetadataExtractor
{
    /**
     * Extract metadata from EPUB file (package.opf).
     */
    public function extract(string $filePath): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        if (! $this->fileExists($filePath)) {
            $this->logExtraction('error', 'EPUB file not found', ['file' => $filePath]);

            return $metadata;
        }

        try {
            $zip = new ZipArchive;
            if ($zip->open($filePath) !== true) {
                throw new \Exception('Failed to open EPUB file as ZIP');
            }

            // Find package.opf file (usually in META-INF/container.xml)
            $opfPath = $this->findOPFFile($zip);

            if (! $opfPath) {
                $this->logExtraction('warning', 'Could not find package.opf in EPUB', ['file' => $filePath]);
                $zip->close();

                return $metadata;
            }

            // Read and parse OPF XML
            $opfContent = $zip->getFromName($opfPath);
            $zip->close();

            if (! $opfContent) {
                throw new \Exception('Failed to read package.opf content');
            }

            $metadata = $this->parseOPF($opfContent);

            $this->logExtraction('info', 'EPUB extraction completed', [
                'file' => $filePath,
                'opf_path' => $opfPath,
                'has_title' => (bool) $metadata->getTitle(),
                'author_count' => count($metadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->logExtraction('error', 'EPUB extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Find the path to package.opf file within EPUB.
     */
    private function findOPFFile(ZipArchive $zip): ?string
    {
        // First, try to read container.xml to find OPF path
        $containerContent = $zip->getFromName('META-INF/container.xml');

        if ($containerContent) {
            try {
                $xml = new \SimpleXMLElement($containerContent);
                $ns = $xml->getNamespaces();

                // Look for rootfile element
                foreach ($xml->children($ns['container'] ?? '') as $element) {
                    if ($element->getName() === 'rootfiles') {
                        foreach ($element->children() as $rootfile) {
                            $path = (string) $rootfile->attributes()['full-path'];
                            if ($path && $zip->locateName($path) !== false) {
                                return $path;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logExtraction('debug', 'Failed to parse container.xml', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: search for any .opf file
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '.opf')) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Parse OPF (Open Packaging Format) XML file.
     */
    private function parseOPF(string $opfContent): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        try {
            $opfContent = $this->normalizeEncoding($opfContent);
            $xml = new \SimpleXMLElement($opfContent);

            // Register DC namespace
            $namespaces = $xml->getNamespaces(true);
            $dcNs = $namespaces['dc'] ?? 'http://purl.org/dc/elements/1.1/';

            // Extract title (dc:title)
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'title') {
                    $title = $this->cleanText((string) $element);
                    if ($title) {
                        $metadata->setTitle($title, 0.95);
                        break;
                    }
                }
            }

            // Extract creators/authors (dc:creator)
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'creator') {
                    $author = $this->cleanText((string) $element);
                    if ($author) {
                        $metadata->addAuthor($author, 0.95);
                    }
                }
            }

            // Extract publisher (dc:publisher)
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'publisher') {
                    $publisher = $this->cleanText((string) $element);
                    if ($publisher) {
                        $metadata->setPublisher($publisher, 0.95);
                        break;
                    }
                }
            }

            // Extract date (dc:issued or dc:date)
            foreach ($xml->children($dcNs) as $element) {
                if (in_array($element->getName(), ['date', 'issued'])) {
                    $dateStr = (string) $element;
                    if ($dateStr) {
                        $year = $this->extractYear($dateStr);
                        if ($year) {
                            $metadata->setPublicationYear($year, 0.9);
                            break;
                        }
                    }
                }
            }

            // Extract language (dc:language)
            foreach ($xml->children($dcNs) as $element) {
                if ($element->getName() === 'language') {
                    // Language info is available but not stored in ExtractedMetadata
                    // Could be used for multilingual support
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logExtraction('warning', 'Failed to parse OPF XML', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }
}
