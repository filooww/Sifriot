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
    private const SUPPORTED_EXTENSIONS = ['pdf', 'doc', 'docx', 'epub', 'fb2', 'txt', 'djvu'];

    /**
     * Minimum characters per page threshold for PDF text detection.
     * PDFs with less text per page are likely image-based/scanned.
     */
    private const MIN_CHARS_PER_PAGE = 50;

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
                'doc' => $this->extractFromDoc($filePath),
                'docx' => $this->extractFromDocx($filePath),
                'epub' => $this->extractFromEpub($filePath),
                'fb2' => $this->extractFromFb2($filePath),
                'txt' => $this->extractFromTxt($filePath),
                // Best-effort: we don't have a reliable pure-PHP DjVu OCR/text extractor.
                // For AI extraction we still try to provide useful context via the filename.
                'djvu' => $this->extractFromDjvu($filePath),
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
        } catch (\RuntimeException $e) {
            // Re-throw runtime exceptions (e.g., image-based PDF detection)
            // These contain user-friendly messages
            $this->log('warning', 'Text extraction blocked', [
                'file' => basename($filePath),
                'extension' => $extension,
                'reason' => $e->getMessage(),
            ]);
            throw $e;
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
     *
     * @throws \RuntimeException If PDF is image-based and has no extractable text
     */
    private function extractFromPdf(string $filePath): string
    {
        if (! class_exists('\Smalot\PdfParser\Parser')) {
            $this->log('warning', 'PDF parser not available');

            return '';
        }

        try {
            // Configure parser to skip image content for memory efficiency
            $config = new \Smalot\PdfParser\Config;
            $config->setRetainImageContent(false);

            $parser = new \Smalot\PdfParser\Parser([], $config);
            $pdf = $parser->parseFile($filePath);

            $pageCount = count($pdf->getPages());
            $pages = $pdf->getPages();

            // Detect image-based PDFs first
            $totalText = '';
            foreach ($pages as $page) {
                $totalText .= $page->getText() . "\n";
            }
            $textLength = strlen(trim($totalText));

            // Detect image-based PDFs: if text per page is below threshold
            if ($pageCount > 0 && $textLength / $pageCount < self::MIN_CHARS_PER_PAGE) {
                $this->log('warning', 'PDF appears to be image-based (scanned)', [
                    'file' => basename($filePath),
                    'pages' => $pageCount,
                    'total_chars' => $textLength,
                    'chars_per_page' => round($textLength / $pageCount, 2),
                ]);

                throw new \RuntimeException(
                    'PDF appears to be image-based or scanned. AI extraction requires text-based PDFs. '.
                    'Consider using OCR software to convert this document first.'
                );
            }

            // For better metadata extraction, prioritize first few pages and last pages
            // where publication info is typically found
            $metadataRichText = '';

            // Extract from first 3 pages (title, publisher, issue info usually here)
            $firstPages = min(3, count($pages));
            for ($i = 0; $i < $firstPages; $i++) {
                $metadataRichText .= $pages[$i]->getText() . "\n";
            }

            // Extract from last 2 pages (publisher info, ISSN, etc. usually here)
            $lastPages = min(2, count($pages));
            $startFromLast = max(0, count($pages) - $lastPages);
            for ($i = $startFromLast; $i < count($pages); $i++) {
                // Avoid duplicates if document is very short
                if ($i >= $firstPages) {
                    $metadataRichText .= $pages[$i]->getText() . "\n";
                }
            }

            // If we got good metadata-rich text, use it; otherwise use all text
            if (strlen(trim($metadataRichText)) > 200) {
                $this->log('info', 'PDF extraction: Using metadata-rich pages', [
                    'file' => basename($filePath),
                    'first_pages' => $firstPages,
                    'last_pages' => $lastPages,
                    'chars_extracted' => strlen(trim($metadataRichText)),
                ]);
                return $metadataRichText;
            }

            $this->log('info', 'PDF extraction: Using all text', [
                'file' => basename($filePath),
                'total_chars' => strlen(trim($totalText)),
            ]);

            return $totalText;
        } catch (\RuntimeException $e) {
            // Re-throw our custom exceptions
            throw $e;
        } catch (\Exception $e) {
            // Handle encrypted or malformed PDFs gracefully
            $this->log('debug', 'PDF text extraction failed', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Extract text from DOC file (legacy Word format).
     */
    private function extractFromDoc(string $filePath): string
    {
        if (! class_exists('\PhpOffice\PhpWord\IOFactory')) {
            $this->log('warning', 'PhpWord not available for DOC extraction');

            return '';
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath, 'MsDoc');
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text .= $this->extractPhpWordElementText($element);
                }
            }

            return $text;
        } catch (\Exception $e) {
            $this->log('debug', 'DOC text extraction failed', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Recursively extract text from PhpWord elements.
     */
    private function extractPhpWordElementText($element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $elementText = $element->getText();
            if (is_string($elementText)) {
                $text .= $elementText;
            }
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $childElement) {
                $text .= $this->extractPhpWordElementText($childElement);
            }
        }

        // Add newline after paragraph-like elements
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun ||
            $element instanceof \PhpOffice\PhpWord\Element\Text) {
            $text .= "\n";
        }

        return $text;
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

            // Prioritize metadata-rich files (title, copyright, toc)
            $metadataFiles = [];
            $regularFiles = [];

            foreach ($contentFiles as $contentFile) {
                $lowerName = strtolower($contentFile);

                // Files likely to contain metadata
                if (preg_match('/(title|copyright|toc|cover|imprint|publisher|publish)/i', $lowerName)) {
                    $metadataFiles[] = $contentFile;
                } else {
                    $regularFiles[] = $contentFile;
                }
            }

            // Process metadata files first, then regular files
            $allFiles = array_merge($metadataFiles, array_slice($regularFiles, 0, 5)); // Limit regular files

            // Extract text from prioritized files
            foreach ($allFiles as $contentFile) {
                $content = $zip->getFromName($contentFile);
                if ($content) {
                    // Strip HTML tags and decode entities
                    $content = $this->normalizeEncoding($content);
                    $content = strip_tags($content);
                    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text .= $content."\n";
                }
            }

            $this->log('info', 'EPUB extraction completed', [
                'metadata_files' => count($metadataFiles),
                'regular_files_processed' => min(5, count($regularFiles)),
                'total_chars' => strlen($text),
            ]);
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
                $this->log('debug', 'FB2 file is empty or could not be read');
                return '';
            }

            // FB2 files often use Windows-1251 encoding
            // First check if there's an XML declaration with encoding
            if (preg_match('/encoding=["\']?([^"\']+)["\']?/i', $content, $matches)) {
                $declaredEncoding = strtoupper($matches[1]);
                if ($declaredEncoding !== 'UTF-8' && $declaredEncoding !== 'UTF8') {
                    // Convert to UTF-8
                    $content = mb_convert_encoding($content, 'UTF-8', $declaredEncoding);
                    // Update XML declaration to UTF-8
                    $content = preg_replace('/encoding=["\']?[^"\']+["\']?/i', 'encoding="UTF-8"', $content);
                }
            } else {
                // No encoding declaration, try to detect
                $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'CP1251', 'ISO-8859-1'], true);
                if ($encoding && $encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                }
            }

            // Additional normalization
            $content = $this->normalizeEncoding($content);

            // Suppress XML warnings for malformed documents
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
            $errors = libxml_get_errors();
            libxml_clear_errors();

            if ($xml === false || !empty($errors)) {
                $this->log('debug', 'FB2 XML parsing failed', [
                    'file' => basename($filePath),
                    'errors' => array_map(function($e) { return trim($e->message); }, $errors),
                ]);

                // Try alternative approach - strip problematic characters and retry
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
                $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
            }

            if ($xml === false) {
                $this->log('debug', 'FB2 XML parsing completely failed', ['file' => basename($filePath)]);
                return '';
            }

            $text = '';

            // Get all namespaces from the XML
            $namespaces = $xml->getNamespaces(true);

            // Find the fictionbook namespace
            $fbNamespace = '';
            $fbNamespacePrefix = '';

            foreach ($namespaces as $prefix => $namespace) {
                if (strpos($namespace, 'gribuser.ru/xml/fictionbook') !== false) {
                    $fbNamespace = $namespace;
                    // Use 'fb' as the prefix for XPath if none exists
                    $fbNamespacePrefix = $prefix ?: 'fb';
                    if ($prefix === '') {
                        // Register the default namespace with 'fb' prefix
                        $xml->registerXPathNamespace('fb', $namespace);
                    } else {
                        // Register existing prefix
                        $xml->registerXPathNamespace($prefix, $namespace);
                    }
                    break;
                }
            }

            // Extract metadata from title-info section (publisher, year, etc.)
            if ($fbNamespace) {
                $titleInfo = $xml->xpath('//fb:description/fb:title-info');
                if ($titleInfo && !empty($titleInfo)) {
                    $metadataText = $this->extractFb2MetadataText($titleInfo[0], $fbNamespace);
                    $text .= $metadataText . "\n";
                }
            }

            // Try to find body element using XPath with the registered prefix
            if ($fbNamespace) {
                $bodyElements = $xml->xpath('//fb:body');
                if ($bodyElements && ! empty($bodyElements)) {
                    foreach ($bodyElements as $body) {
                        $text .= $this->extractFb2TextRecursive($body);
                    }
                }
            }

            // If not found via XPath, try direct children access
            if (empty($text)) {
                // Try with the found namespace
                if ($fbNamespace) {
                    $children = $xml->children($fbNamespace, true);
                    foreach ($children as $child) {
                        if ($child->getName() === 'body') {
                            $text .= $this->extractFb2TextRecursive($child);
                            break;
                        }
                    }
                }

                // Try without namespace (non-namespaced FB2)
                if (empty($text)) {
                    foreach ($xml->children() as $child) {
                        if ($child->getName() === 'body') {
                            $text .= $this->extractFb2TextRecursive($child);
                            break;
                        }
                    }
                }
            }

            if (empty($text)) {
                $this->log('debug', 'FB2 extraction returned no text', [
                    'file' => basename($filePath),
                    'namespaces' => array_values($namespaces),
                ]);
            }

            return $text;
        } catch (\Exception $e) {
            $this->log('debug', 'FB2 text extraction failed', [
                'error' => $e->getMessage(),
                'file' => basename($filePath),
                'trace' => $e->getTraceAsString()
            ]);

            return '';
        }
    }

    /**
     * Extract metadata text from FB2 title-info section.
     */
    private function extractFb2MetadataText(\SimpleXMLElement $titleInfo, string $fbNamespace): string
    {
        $metadataText = '';

        try {
            // Extract book title
            $bookTitle = $titleInfo->xpath('fb:book-title');
            if ($bookTitle && !empty($bookTitle)) {
                $metadataText .= "Title: " . trim((string)$bookTitle[0]) . "\n";
            }

            // Extract authors
            $authors = $titleInfo->xpath('fb:author');
            if ($authors && !empty($authors)) {
                $authorNames = [];
                foreach ($authors as $author) {
                    $firstName = $author->xpath('fb:first-name');
                    $lastName = $author->xpath('fb:last-name');
                    $middleName = $author->xpath('fb:middle-name');

                    $nameParts = [];
                    if ($firstName && !empty($firstName)) {
                        $nameParts[] = trim((string)$firstName[0]);
                    }
                    if ($middleName && !empty($middleName)) {
                        $nameParts[] = trim((string)$middleName[0]);
                    }
                    if ($lastName && !empty($lastName)) {
                        $nameParts[] = trim((string)$lastName[0]);
                    }

                    if (!empty($nameParts)) {
                        $authorNames[] = implode(' ', $nameParts);
                    }
                }
                if (!empty($authorNames)) {
                    $metadataText .= "Authors: " . implode(', ', $authorNames) . "\n";
                }
            }

            // Extract genre
            $genres = $titleInfo->xpath('fb:genre');
            if ($genres && !empty($genres)) {
                $genreTexts = [];
                foreach ($genres as $genre) {
                    $genreTexts[] = trim((string)$genre);
                }
                if (!empty($genreTexts)) {
                    $metadataText .= "Genres: " . implode(', ', $genreTexts) . "\n";
                }
            }

            // Extract year
            $years = $titleInfo->xpath('fb:date');
            if ($years && !empty($years)) {
                foreach ($years as $year) {
                    $yearValue = trim((string)$year);
                    if (preg_match('/\d{4}/', $yearValue, $matches)) {
                        $metadataText .= "Year: " . $matches[0] . "\n";
                        break;
                    }
                }
            }

            // Extract publisher info from publish-info section
            $description = $titleInfo->xpath('//fb:description');
            if ($description && !empty($description)) {
                $publishInfo = $description[0]->xpath('fb:publish-info');
                if ($publishInfo && !empty($publishInfo)) {
                    $publisher = $publishInfo[0]->xpath('fb:publisher');
                    if ($publisher && !empty($publisher)) {
                        $metadataText .= "Publisher: " . trim((string)$publisher[0]) . "\n";
                    }

                    $year = $publishInfo[0]->xpath('fb:year');
                    if ($year && !empty($year)) {
                        $metadataText .= "Publish Year: " . trim((string)$year[0]) . "\n";
                    }

                    $isbn = $publishInfo[0]->xpath('fb:isbn');
                    if ($isbn && !empty($isbn)) {
                        $metadataText .= "ISBN: " . trim((string)$isbn[0]) . "\n";
                    }
                }
            }

        } catch (\Exception $e) {
            // If metadata extraction fails, continue with body text extraction
            $this->log('debug', 'FB2 metadata extraction failed', ['error' => $e->getMessage()]);
        }

        return $metadataText;
    }

    /**
     * Recursively extract text from FB2 XML.
     */
    private function extractFb2TextRecursive(\SimpleXMLElement $element): string
    {
        $text = '';

        // Get the namespace of the current element
        $elementNs = $element->getNamespaces(true);
        $currentNamespace = '';

        // Find the fictionbook namespace
        foreach ($elementNs as $prefix => $namespace) {
            if (strpos($namespace, 'gribuser.ru/xml/fictionbook') !== false) {
                $currentNamespace = $namespace;
                break;
            }
        }

        // Get children - try with namespace first, then without
        $children = [];

        if ($currentNamespace) {
            // Get children in the same namespace as the parent
            $namespacedChildren = $element->children($currentNamespace, true);
            foreach ($namespacedChildren as $child) {
                $children[] = $child;
            }
        }

        // Also get non-namespaced children (for mixed content or non-namespaced FB2)
        foreach ($element->children() as $child) {
            // Check if this child was already processed
            $alreadyProcessed = false;
            foreach ($children as $existing) {
                if ($existing === $child) {
                    $alreadyProcessed = true;
                    break;
                }
            }
            if (! $alreadyProcessed) {
                $children[] = $child;
            }
        }

        // Process children
        foreach ($children as $child) {
            $name = $child->getName();

            // Text elements - extract text directly
            if (in_array($name, ['p', 'v', 'subtitle', 'text-author'])) {
                $text .= trim((string) $child)."\n";
            }
            // Container elements to recurse into
            elseif (in_array($name, ['section', 'stanza', 'poem', 'cite', 'epigraph', 'body', 'title'])) {
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
     * Best-effort "text" extraction for DJVU:
     * derive a prompt-friendly string from the filename.
     *
     * Example:
     *   "Vaynrub_Frontovye-sudby.853171.djvu" -> "Vaynrub Frontovye - sudby"
     */
    private function extractFromDjvu(string $filePath): string
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        if ($filename === '') {
            return '';
        }

        $filename = $this->normalizeEncoding($filename);

        // Common pattern in your dataset: "...<name>.<digits>.djvu"
        // Strip trailing numeric identifiers from the filename.
        $filename = preg_replace('/(\.|_)?\d+$/u', '', $filename) ?? $filename;

        // Make separators more "text-like" for the LLM.
        $filename = str_replace(['_', '.'], [' ', ' '], $filename);
        $filename = preg_replace('/\s+/u', ' ', $filename) ?? $filename;
        $filename = trim($filename);

        return $filename;
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
