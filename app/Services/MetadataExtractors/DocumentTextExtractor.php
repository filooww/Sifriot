<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class DocumentTextExtractor
{
    /**
     * Supported file extensions.
     */
    private const SUPPORTED_EXTENSIONS = ['pdf', 'docx', 'epub', 'fb2', 'txt'];

    /**
     * Extract text content from a document file.
     *
     * @param  string  $filePath  Path to the document file
     * @param  int  $maxChars  Maximum characters to extract
     * @return string Extracted text content
     */
    public function extractText(string $filePath, int $maxChars = 5000): string
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $this->log('warning', 'File not found or not readable', ['file' => $filePath]);

            return '';
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! in_array($extension, self::SUPPORTED_EXTENSIONS)) {
            $this->log('info', 'Unsupported file extension for text extraction', [
                'file' => $filePath,
                'extension' => $extension,
            ]);

            return '';
        }

        try {
            $text = match ($extension) {
                'pdf' => $this->extractFromPdf($filePath),
                'docx' => $this->extractFromDocx($filePath),
                'epub' => $this->extractFromEpub($filePath),
                'fb2' => $this->extractFromFb2($filePath),
                'txt' => $this->extractFromTxt($filePath),
                default => '',
            };

            // Normalize and truncate
            $text = $this->normalizeText($text);
            $text = $this->truncateToLimit($text, $maxChars);

            $this->log('info', 'Text extraction completed', [
                'file' => basename($filePath),
                'extension' => $extension,
                'chars_extracted' => strlen($text),
                'truncated' => strlen($text) >= $maxChars,
            ]);

            return $text;
        } catch (\Exception $e) {
            $this->log('error', 'Text extraction failed', [
                'file' => $filePath,
                'extension' => $extension,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Check if file type is supported for text extraction.
     */
    public function supportsFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return in_array($extension, self::SUPPORTED_EXTENSIONS);
    }

    /**
     * Extract text from PDF file.
     */
    private function extractFromPdf(string $filePath): string
    {
        if (! class_exists('\Smalot\PdfParser\Parser')) {
            $this->log('warning', 'PDF parser not available');

            return '';
        }

        try {
            $parser = new \Smalot\PdfParser\Parser;
            $pdf = $parser->parseFile($filePath);

            return $pdf->getText();
        } catch (\Exception $e) {
            // Handle encrypted or malformed PDFs gracefully
            $this->log('debug', 'PDF text extraction failed', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Extract text from DOCX file.
     */
    private function extractFromDocx(string $filePath): string
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return '';
        }

        $text = '';

        try {
            $docContent = $zip->getFromName('word/document.xml');
            if ($docContent) {
                $docContent = $this->normalizeEncoding($docContent);
                $xml = new \SimpleXMLElement($docContent);

                $namespaces = $xml->getNamespaces(true);
                $wNs = $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

                // Extract text from all paragraphs
                $text = $this->extractDocxTextRecursive($xml, $wNs);
            }
        } catch (\Exception $e) {
            $this->log('debug', 'DOCX text extraction failed', ['error' => $e->getMessage()]);
        } finally {
            $zip->close();
        }

        return $text;
    }

    /**
     * Recursively extract text from DOCX XML.
     */
    private function extractDocxTextRecursive(\SimpleXMLElement $element, string $wNs): string
    {
        $text = '';

        foreach ($element->children($wNs) as $child) {
            if ($child->getName() === 't') {
                $text .= (string) $child;
            } elseif ($child->getName() === 'p') {
                $text .= $this->extractDocxTextRecursive($child, $wNs)."\n";
            } elseif ($child->getName() === 'r') {
                $text .= $this->extractDocxTextRecursive($child, $wNs);
            } elseif ($child->getName() === 'body') {
                $text .= $this->extractDocxTextRecursive($child, $wNs);
            }
        }

        return $text;
    }

    /**
     * Extract text from EPUB file.
     */
    private function extractFromEpub(string $filePath): string
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return '';
        }

        $text = '';

        try {
            // Find content files (XHTML)
            $contentFiles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('/\.(xhtml|html|htm)$/i', $name)) {
                    $contentFiles[] = $name;
                }
            }

            // Sort to get chapters in order (usually named chapter1.xhtml, etc.)
            sort($contentFiles);

            // Extract text from each content file
            foreach ($contentFiles as $contentFile) {
                $content = $zip->getFromName($contentFile);
                if ($content) {
                    // Strip HTML tags and decode entities
                    $content = $this->normalizeEncoding($content);
                    $content = strip_tags($content);
                    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text .= $content."\n";
                }
            }
        } catch (\Exception $e) {
            $this->log('debug', 'EPUB text extraction failed', ['error' => $e->getMessage()]);
        } finally {
            $zip->close();
        }

        return $text;
    }

    /**
     * Extract text from FB2 file.
     */
    private function extractFromFb2(string $filePath): string
    {
        try {
            $content = file_get_contents($filePath);
            if (! $content) {
                return '';
            }

            $content = $this->normalizeEncoding($content);
            $xml = new \SimpleXMLElement($content);

            $text = '';

            // Find body element and extract text from sections
            foreach ($xml->children() as $element) {
                if ($element->getName() === 'body') {
                    $text .= $this->extractFb2TextRecursive($element);
                }
            }

            return $text;
        } catch (\Exception $e) {
            $this->log('debug', 'FB2 text extraction failed', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Recursively extract text from FB2 XML.
     */
    private function extractFb2TextRecursive(\SimpleXMLElement $element): string
    {
        $text = '';

        foreach ($element->children() as $child) {
            $name = $child->getName();

            if (in_array($name, ['p', 'v', 'subtitle', 'text-author'])) {
                $text .= trim((string) $child)."\n";
            } elseif (in_array($name, ['section', 'stanza', 'poem', 'cite', 'epigraph'])) {
                $text .= $this->extractFb2TextRecursive($child);
            }
        }

        return $text;
    }

    /**
     * Extract text from TXT file.
     */
    private function extractFromTxt(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return '';
        }

        return $this->normalizeEncoding($content);
    }

    /**
     * Normalize text encoding to UTF-8.
     */
    private function normalizeEncoding(string $text): string
    {
        if (! mb_check_encoding($text, 'UTF-8')) {
            $encoding = mb_detect_encoding($text, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'ASCII'], true);
            if ($encoding !== false) {
                $text = mb_convert_encoding($text, 'UTF-8', $encoding);
            } else {
                // Fallback to Windows-1251 (common for Russian texts)
                $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1251');
            }
        }

        return $text;
    }

    /**
     * Normalize text: remove extra whitespace, control characters.
     */
    private function normalizeText(string $text): string
    {
        // Remove control characters except newlines and tabs
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Normalize multiple spaces/newlines
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Truncate text to limit at word boundary.
     */
    private function truncateToLimit(string $text, int $maxChars): string
    {
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        // Find last word boundary before limit
        $truncated = substr($text, 0, $maxChars);
        $lastSpace = strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > $maxChars * 0.8) {
            return substr($truncated, 0, $lastSpace);
        }

        return $truncated;
    }

    /**
     * Log extraction activity.
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('folder_scan')->{$level}("[DocumentTextExtractor] {$message}", $context);
    }
}
