<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Publication;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileViewController extends Controller
{
    /**
     * Convert FB2 file to HTML
     */
    public function convertFb2(int $publication, string $filename): Response|JsonResponse
    {
        // Check authentication
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        // Decode URL-safe base64-encoded filename
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            return response()->json([
                'error' => 'Invalid filename encoding',
            ], 400);
        }

        // Try to find the file
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->first();

        if (!$file) {
            return response()->json([
                'error' => 'File not found in database',
            ], 404);
        }

        // Verify publication exists and is accessible
        $pub = Publication::findOrFail($publication);

        if ($pub->status !== 'published' && Auth::user()->role !== 'admin') {
            throw new AuthorizationException('Cannot view this publication');
        }

        // Get file path (same logic as convertDoc)
        // Get file path (same logic as convertDoc)
        if ($file->file_path) {
            $disk = 'library';
            $storagePath = $file->file_path;
        } else {
            $fileSource = $file->file_source;

            if (pathinfo($fileSource, PATHINFO_EXTENSION)) {
                $disk = 'local';
                $storagePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
            } elseif ($fileSource === 'bulk_scan') {
                $disk = 'library';
                $allFiles = Storage::disk($disk)->allFiles();
                $storagePath = null;
                foreach ($allFiles as $filePath) {
                    if (basename($filePath) === $decodedFilename) {
                        $storagePath = $filePath;
                        break;
                    }
                }
                if ($storagePath === null) {
                    abort(404, 'File not found in library storage');
                }
            } else {
                $disk = 'library';
                $storagePath = $fileSource . '/' . $decodedFilename;
            }
        }

        if (!Storage::disk($disk)->exists($storagePath)) {
            abort(404, 'File not found in storage');
        }

        try {
            // Read and parse FB2 XML
            $xmlContent = Storage::disk($disk)->get($storagePath);

            // Load XML with namespace support
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new \Exception('Failed to parse FB2 XML: ' . ($errors[0]->message ?? 'Unknown error'));
            }

            // Get the namespaces from the document
            $namespaces = $xml->getNamespaces(true);
            $fbNamespace = $namespaces[''] ?? 'http://www.gribuser.ru/xml/fictionbook/2.0';

            // Register FB2 namespace
            $xml->registerXPathNamespace('fb', $fbNamespace);

            // Extract book information
            $titleNodes = $xml->xpath('//fb:title-info/fb:book-title');
            $title = !empty($titleNodes) ? (string) $titleNodes[0] : 'Untitled';

            $authors = $xml->xpath('//fb:title-info/fb:author');
            $authorNames = [];
            if (!empty($authors)) {
                foreach ($authors as $author) {
                    $author->registerXPathNamespace('fb', $fbNamespace);
                    $firstNameNodes = $author->xpath('fb:first-name');
                    $lastNameNodes = $author->xpath('fb:last-name');
                    $firstName = !empty($firstNameNodes) ? (string) $firstNameNodes[0] : '';
                    $lastName = !empty($lastNameNodes) ? (string) $lastNameNodes[0] : '';
                    if ($firstName || $lastName) {
                        $authorNames[] = trim("$firstName $lastName");
                    }
                }
            }

            $annotation = $xml->xpath('//fb:title-info/fb:annotation');

            // Build HTML output
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>';
            $html .= 'body { font-family: Georgia, serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 2rem; background: #f9fafb; color: #1f2937; }';
            $html .= 'h1 { font-size: 2rem; margin-bottom: 0.5rem; color: #111827; }';
            $html .= '.author { font-size: 1.25rem; color: #6b7280; margin-bottom: 2rem; }';
            $html .= '.annotation { background: #fff; padding: 1.5rem; border-left: 4px solid #3b82f6; margin-bottom: 2rem; border-radius: 0.5rem; }';
            $html .= '.content { background: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }';
            $html .= 'section { margin-bottom: 2rem; }';
            $html .= 'section > title { display: block; font-size: 1.5rem; font-weight: bold; margin: 2rem 0 1rem; color: #111827; }';
            $html .= 'p { margin-bottom: 1rem; text-align: justify; }';
            $html .= 'empty-line { display: block; height: 1rem; }';
            $html .= '@media (prefers-color-scheme: dark) {';
            $html .= '  body { background: #111827; color: #e5e7eb; }';
            $html .= '  h1 { color: #f9fafb; }';
            $html .= '  .content, .annotation { background: #1f2937; color: #e5e7eb; }';
            $html .= '  section > title { color: #f9fafb; }';
            $html .= '}';
            $html .= '</style></head><body>';

            // Add title and author
            $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
            if (!empty($authorNames)) {
                $html .= '<div class="author">' . htmlspecialchars(implode(', ', $authorNames)) . '</div>';
            }

            // Add annotation if available
            if (!empty($annotation)) {
                $html .= '<div class="annotation">';
                foreach ($annotation[0]->children($fbNamespace) as $child) {
                    if ($child->getName() === 'p') {
                        $html .= '<p>' . htmlspecialchars((string) $child) . '</p>';
                    }
                }
                $html .= '</div>';
            }

            // Process body content
            $html .= '<div class="content">';
            $bodies = $xml->xpath('//fb:body');
            if (!empty($bodies)) {
                foreach ($bodies as $body) {
                    $html .= $this->processFb2Node($body, $fbNamespace);
                }
            }
            $html .= '</div>';

            $html .= '</body></html>';

            // Return as HTML
            return response($html)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('Cache-Control', 'public, max-age=3600');

        } catch (\Exception $e) {
            Log::error('FB2 conversion failed', [
                'publication_id' => $publication,
                'filename' => $decodedFilename,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to convert FB2 file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recursively process FB2 XML nodes to HTML
     */
    private function processFb2Node(\SimpleXMLElement $node, string $namespace): string
    {
        $html = '';

        foreach ($node->children($namespace) as $child) {
            $nodeName = $child->getName();

            switch ($nodeName) {
                case 'section':
                    $html .= '<section>';
                    $html .= $this->processFb2Node($child, $namespace);
                    $html .= '</section>';
                    break;

                case 'title':
                    $html .= '<title>';
                    $html .= $this->processFb2Node($child, $namespace);
                    $html .= '</title>';
                    break;

                case 'p':
                    $html .= '<p>' . htmlspecialchars((string) $child) . '</p>';
                    break;

                case 'empty-line':
                    $html .= '<empty-line></empty-line>';
                    break;

                default:
                    // Recursively process unknown nodes
                    $html .= $this->processFb2Node($child, $namespace);
                    break;
            }
        }

        return $html;
    }

    /**
     * Convert DOC file to text using antiword
     */
    public function convertDoc(int $publication, string $filename): Response|JsonResponse
    {
        // Check authentication
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        // Decode URL-safe base64-encoded filename
        // Add back padding and convert URL-safe characters back to standard base64
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            return response()->json([
                'error' => 'Invalid filename encoding',
            ], 400);
        }

        // Try to find the file
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->first();

        if (!$file) {
            return response()->json([
                'error' => 'File not found in database',
            ], 404);
        }

        // Verify publication exists and is accessible
        $pub = Publication::findOrFail($publication);

        if ($pub->status !== 'published' && Auth::user()->role !== 'admin') {
            throw new AuthorizationException('Cannot view this publication');
        }

        // Get file path
        if ($file->file_path) {
            $disk = 'library';
            $storagePath = $file->file_path;
        } else {
            $fileSource = $file->file_source;

            if (pathinfo($fileSource, PATHINFO_EXTENSION)) {
                $disk = 'local';
                $storagePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
            } elseif ($fileSource === 'bulk_scan') {
                // Legacy bulk_scan files: search recursively in library disk (for backwards compatibility)
                $disk = 'library';

                // Search for the file in the library directory
                $allFiles = Storage::disk($disk)->allFiles();
                $storagePath = null;

                foreach ($allFiles as $filePath) {
                    if (basename($filePath) === $decodedFilename) {
                        $storagePath = $filePath;
                        break;
                    }
                }

                if ($storagePath === null) {
                    abort(404, 'File not found in library storage');
                }
            } else {
                // New bulk scanned file: file_source is the relative directory path on library disk
                $disk = 'library';
                $storagePath = $fileSource . '/' . $decodedFilename;
            }
        }

        if (!Storage::disk($disk)->exists($storagePath)) {
            abort(404, 'File not found in storage');
        }

        // Get full path and convert using LibreOffice
        $fullPath = Storage::disk($disk)->path($storagePath);

        try {
            $html = $this->convertDocWithLibreOffice($fullPath);

            // Extract text content from the HTML for plain text endpoint
            $textContent = strip_tags($html);
            $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $textContent = preg_replace('/\n{3,}/', "\n\n", trim($textContent));

            return response($textContent)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Cache-Control', 'public, max-age=3600');

        } catch (\Exception $e) {
            Log::error('DOC conversion failed', [
                'publication_id' => $publication,
                'filename' => $decodedFilename,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to convert DOC file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert DOC file to styled HTML using PHPWord
     */
    public function convertDocToHtml(int $publication, string $filename): Response|JsonResponse
    {
        // Check authentication
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        // Decode URL-safe base64-encoded filename
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            return response()->json([
                'error' => 'Invalid filename encoding',
            ], 400);
        }

        // Try to find the file
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->first();

        if (!$file) {
            return response()->json([
                'error' => 'File not found in database',
            ], 404);
        }

        // Verify publication exists and is accessible
        $pub = Publication::findOrFail($publication);

        if ($pub->status !== 'published' && Auth::user()->role !== 'admin') {
            throw new AuthorizationException('Cannot view this publication');
        }

        // Get file path
        if ($file->file_path) {
            $disk = 'library';
            $storagePath = $file->file_path;
        } else {
            $fileSource = $file->file_source;

            if (pathinfo($fileSource, PATHINFO_EXTENSION)) {
                $disk = 'local';
                $storagePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
            } elseif ($fileSource === 'bulk_scan') {
                $disk = 'library';
                $allFiles = Storage::disk($disk)->allFiles();
                $storagePath = null;
                foreach ($allFiles as $filePath) {
                    if (basename($filePath) === $decodedFilename) {
                        $storagePath = $filePath;
                        break;
                    }
                }
                if ($storagePath === null) {
                    abort(404, 'File not found in library storage');
                }
            } else {
                $disk = 'library';
                $storagePath = $fileSource . '/' . $decodedFilename;
            }
        }

        if (!Storage::disk($disk)->exists($storagePath)) {
            abort(404, 'File not found in storage');
        }

        try {
            $fullPath = Storage::disk($disk)->path($storagePath);

            // Try LibreOffice first as primary converter
            try {
                $libreOfficeHtml = $this->convertDocWithLibreOffice($fullPath);

                // Extract body content
                $bodyContent = $libreOfficeHtml;
                if (preg_match('/<body[^>]*>(.*)<\/body>/si', $libreOfficeHtml, $matches)) {
                    $bodyContent = $matches[1];
                }

                // Wrap in styled template
                $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
                $html .= '<style>';
                $html .= 'body { font-family: "Segoe UI", "Calibri", sans-serif; line-height: 1.6; max-width: 900px; margin: 0 auto; padding: 2rem; background: #f9fafb; color: #1f2937; }';
                $html .= 'h1, h2, h3, h4, h5, h6 { color: #111827; margin-top: 1.5rem; margin-bottom: 0.5rem; }';
                $html .= 'p { margin-bottom: 1rem; text-align: justify; }';
                $html .= 'table { border-collapse: collapse; width: 100%; margin-bottom: 1.5rem; }';
                $html .= 'table th, table td { border: 1px solid #d1d5db; padding: 0.75rem; text-align: left; }';
                $html .= '.content { background: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }';
                $html .= '@media (prefers-color-scheme: dark) {';
                $html .= '  body { background: #111827; color: #e5e7eb; }';
                $html .= '  .content { background: #1f2937; color: #e5e7eb; }';
                $html .= '}';
                $html .= '</style></head><body><div class="content">';
                $html .= $bodyContent;
                $html .= '</div></body></html>';

                return response($html)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Cache-Control', 'public, max-age=3600');

            } catch (\Exception $loException) {
                Log::warning('LibreOffice conversion failed, trying PHPWord fallback', [
                    'publication_id' => $publication,
                    'filename' => $decodedFilename,
                    'error' => $loException->getMessage(),
                ]);

                // Fallback: Try PHPWord if LibreOffice fails
                try {
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($fullPath);
                } catch (\Exception $e) {
                    try {
                        $reader = \PhpOffice\PhpWord\IOFactory::createReader('MsDoc');
                        if ($reader->canRead($fullPath)) {
                            $phpWord = $reader->load($fullPath);
                        } else {
                            throw $e;
                        }
                    } catch (\Exception $e2) {
                        throw new \Exception('Both LibreOffice and PHPWord conversion failed. LibreOffice error: ' . $loException->getMessage());
                    }
                }

                // Use PHPWord's HTML writer
                $tempFile = tempnam(sys_get_temp_dir(), 'phpword_') . '.html';
                $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
                $htmlWriter->save($tempFile);
                $generatedHtml = file_get_contents($tempFile);
                @unlink($tempFile);

                // Extract body and return (reuse previous styling logic if needed, or simple return)
                $bodyContent = $generatedHtml;
                if (preg_match('/<body[^>]*>(.*)<\/body>/si', $generatedHtml, $matches)) {
                    $bodyContent = $matches[1];
                }

                $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
                $html .= '<style>body { font-family: sans-serif; padding: 20px; }</style>';
                $html .= '</head><body>' . $bodyContent . '</body></html>';

                return response($html)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Cache-Control', 'no-cache');
            }

        } catch (\Exception $e) {
            Log::error('DOC to HTML conversion failed', [
                'publication_id' => $publication,
                'filename' => $decodedFilename,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to convert DOC file',
                'message' => $e->getMessage(),
            ], 500)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
    }

    /**
     * Convert DOC file to HTML using LibreOffice headless mode.
     *
     * @throws \Exception if conversion fails
     */
    private function convertDocWithLibreOffice(string $fullPath): string
    {
        $soffice = config('services.libreoffice.path', 'soffice');

        // Create a temp directory for the output
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lo_convert_' . uniqid();
        if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
            throw new \Exception('Failed to create temp directory for conversion');
        }

        // Create a safe temporary copy of the input file to avoid filename issues
        // (LibreOffice fails with spaces/Cyrillic in filenames on Windows CLI)
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'doc';
        $safeInputPath = $tempDir . DIRECTORY_SEPARATOR . 'input_' . uniqid() . '.' . $extension;

        if (!copy($fullPath, $safeInputPath)) {
            @rmdir($tempDir);
            throw new \Exception('Failed to create temporary copy of input file');
        }

        try {
            // Run LibreOffice headless conversion on the SAFE temp file
            $command = sprintf(
                '%s --headless --convert-to html --outdir %s %s 2>&1',
                escapeshellarg($soffice),
                escapeshellarg($tempDir),
                escapeshellarg($safeInputPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('LibreOffice conversion failed (exit code ' . $returnCode . '): ' . implode("\n", $output));
            }

            // Find the generated HTML file
            $htmlFiles = glob($tempDir . DIRECTORY_SEPARATOR . '*.html');
            if (empty($htmlFiles)) {
                throw new \Exception('LibreOffice did not generate an HTML file');
            }

            $htmlContent = file_get_contents($htmlFiles[0]);
            if ($htmlContent === false) {
                throw new \Exception('Failed to read LibreOffice output');
            }

            return $htmlContent;
        } finally {
            // Clean up temp files
            $files = glob($tempDir . DIRECTORY_SEPARATOR . '*');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
            @rmdir($tempDir);
        }
    }

    /**
     * Convert PHPWord element to HTML
     */
    private function convertWordElement($element): string
    {
        $html = '';

        // Use reflection to check element type
        $elementClass = get_class($element);

        // Handle paragraphs
        if (strpos($elementClass, 'Paragraph') !== false || $elementClass === 'PhpOffice\PhpWord\Element\Paragraph') {
            $html .= '<p>';
            foreach ($element->getElements() as $childElement) {
                $childClass = get_class($childElement);
                if (strpos($childClass, 'Text') !== false || $childClass === 'PhpOffice\PhpWord\Element\Text') {
                    $text = htmlspecialchars($childElement->getText());
                    $style = $childElement->getStyle();

                    if ($style) {
                        $classes = [];
                        if ($style->getBold()) {
                            $classes[] = '<strong>';
                        }
                        if ($style->getItalic()) {
                            $classes[] = '<em>';
                        }
                        if ($style->getUnderline() !== 'none') {
                            $classes[] = '<u>';
                        }

                        if (!empty($classes)) {
                            $openTag = implode('', $classes);
                            $closeTags = array_reverse($classes);
                            $closeTag = str_replace(['<', '>'], ['</', '>'], implode('', $closeTags));
                            $text = $openTag . $text . $closeTag;
                        }
                    }
                    $html .= $text;
                }
            }
            $html .= '</p>';

        } // Handle tables
        elseif (strpos($elementClass, 'Table') !== false || $elementClass === 'PhpOffice\PhpWord\Element\Table') {
            $html .= '<table>';
            foreach ($element->getRows() as $row) {
                $html .= '<tr>';
                foreach ($row->getCells() as $cell) {
                    $html .= '<td>';
                    foreach ($cell->getElements() as $cellElement) {
                        $html .= $this->convertWordElement($cellElement);
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

        } // Handle lists (basic)
        elseif (strpos($elementClass, 'ListItem') !== false) {
            $html .= '<li>' . htmlspecialchars($element->getText()) . '</li>';

        } // Handle TextBreak first (because 'TextBreak' contains 'Text')
        elseif (strpos($elementClass, 'TextBreak') !== false || $elementClass === 'PhpOffice\PhpWord\Element\TextBreak') {
            $html .= '<br>';

        } // Handle straight Text elements (common in MsDoc reader)
        elseif (strpos($elementClass, 'Text') !== false || $elementClass === 'PhpOffice\PhpWord\Element\Text') {
            $html .= '<p>' . htmlspecialchars($element->getText()) . '</p>';

        }

        return $html;
    }

    /**
     * Serve cover image (public access, no authentication required)
     */
    public function serveCover(int $publication, string $filename): Response
    {
        // Decode URL-safe base64-encoded filename
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            abort(400, 'Invalid filename encoding');
        }

        // Find the cover file by original file_name
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->where('file_type', 'cover')
            ->first();

        if (!$file) {
            abort(404, 'Cover image not found');
        }

        // For cover images, use file_path if available (uploaded files), otherwise use file_source logic
        if ($file->file_path) {
            // Uploaded cover: file_path contains the actual storage path
            // Check if it's in public storage (covers/) or private storage (content/)
            if (str_starts_with($file->file_path, 'covers/')) {
                $disk = 'public';
                $storagePath = $file->file_path;
            } else {
                $disk = 'local';
                $storagePath = $file->file_path;
            }
        } else {
            // Fallback to file_source logic for bulk scanned covers
            $fileSource = $file->file_source;

            if (pathinfo($fileSource, PATHINFO_EXTENSION)) {
                $disk = 'local';
                $storagePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
            } else {
                $disk = 'library';
                $storagePath = $fileSource . '/' . $decodedFilename;
            }
        }

        if (!Storage::disk($disk)->exists($storagePath)) {
            abort(404, 'Cover image file not found in storage');
        }

        // Get file content
        $content = Storage::disk($disk)->get($storagePath);
        $mimeType = $file->mime_type ?? 'image/jpeg';

        // Return image with caching headers
        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400') // Cache for 24 hours
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * View a file inline (for document viewers)
     */
    public function view(int $publication, string $filename): Response|JsonResponse
    {
        Log::info("FileViewController::view called for pub {$publication}");
        // Check authentication
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        // Decode URL-safe base64-encoded filename (handles Cyrillic characters)
        // Add back padding and convert URL-safe characters back to standard base64
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            return response()->json([
                'error' => 'Invalid filename encoding',
                'received' => $filename,
            ], 400);
        }

        // Debug: Log the decoded filename
        Log::info('FileViewController: Decoded filename', [
            'publication' => $publication,
            'encoded' => $filename,
            'decoded' => $decodedFilename,
        ]);

        // Try to find the file
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->first();

        // If not found, log all files for this publication for debugging
        if (!$file) {
            $allFiles = File::where('id_publication', $publication)->get(['file_name']);
            $debugInfo = [
                'publication_id' => $publication,
                'requested_filename' => $decodedFilename,
                'available_files' => $allFiles->pluck('file_name')->toArray(),
            ];

            Log::error('File not found in database', $debugInfo);

            // Return JSON response with debug info
            return response()->json([
                'error' => 'File not found in database',
                'debug' => $debugInfo,
            ], 404);
        }

        // Verify publication exists and is accessible
        $pub = Publication::findOrFail($publication);

        // Check if publication is published or user is admin
        if ($pub->status !== 'published' && Auth::user()->role !== 'admin') {
            throw new AuthorizationException('Cannot view this publication');
        }

        // Get the file path from storage
        // Use 'library' disk for bulk scanned files, 'local' disk for uploaded files

        // Prioritize file_path if available (standard for new system)
        if ($file->file_path) {
            $disk = 'library';
            $storagePath = $file->file_path;
        } else {
            // Fallback for legacy items without file_path
            $fileSource = $file->file_source;

            // Check if file_source is an absolute path (Unix: /foo or Windows: C:\foo or C:/foo)
            if ($this->isAbsolutePath($fileSource)) {
                // Normalize path separators for consistent comparison
                $normalizedSource = str_replace('\\', '/', $fileSource);

                // Check if file exists directly on filesystem
                if (file_exists($fileSource)) {
                    // Return the file directly from absolute path
                    $content = file_get_contents($fileSource);
                    $mimeType = $file->mime_type ?? 'application/octet-stream';

                    return response($content)
                        ->header('Content-Type', $mimeType)
                        ->header('Content-Disposition', 'inline; filename="' . basename($filename) . '"')
                        ->header('Cache-Control', 'public, max-age=3600')
                        ->header('Access-Control-Allow-Origin', '*');
                }

                // Try to extract relative path from library disk root
                $libraryRoot = str_replace('\\', '/', config('filesystems.disks.library.root', ''));
                if ($libraryRoot && str_starts_with($normalizedSource, $libraryRoot)) {
                    $disk = 'library';
                    $storagePath = ltrim(substr($normalizedSource, strlen($libraryRoot)), '/');
                } else {
                    // Try local storage root
                    $localRoot = str_replace('\\', '/', storage_path('app'));
                    if (str_starts_with($normalizedSource, $localRoot)) {
                        $disk = 'local';
                        $storagePath = ltrim(substr($normalizedSource, strlen($localRoot)), '/');
                    } else {
                        abort(404, 'File not found: absolute path not in known disk roots. Source: ' . $fileSource);
                    }
                }
            } elseif (pathinfo($fileSource, PATHINFO_EXTENSION)) {
                // Relative path with extension - use local disk with content/ prefix if not already included
                $disk = 'local';
                $storagePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
            } elseif ($fileSource === 'bulk_scan') {
                // Legacy bulk_scan files: search recursively in library disk (for backwards compatibility)
                $disk = 'library';

                // Search for the file in the library directory
                $allFiles = Storage::disk($disk)->allFiles();
                $storagePath = null;

                foreach ($allFiles as $filePath) {
                    if (basename($filePath) === $decodedFilename) {
                        $storagePath = $filePath;
                        break;
                    }
                }

                if ($storagePath === null) {
                    Log::error('Bulk scanned file not found in library', [
                        'publication_id' => $publication,
                        'filename' => $decodedFilename,
                        'searched_in' => Storage::disk($disk)->path(''),
                    ]);
                    abort(404, 'File not found in library storage');
                }
            } else {
                // New bulk scanned file: file_source is the relative directory path on library disk
                $disk = 'library';
                $storagePath = $fileSource . '/' . $decodedFilename;
            }
        }

        // Debug logging
        Log::info('File viewer request', [
            'publication_id' => $publication,
            'filename' => $decodedFilename,
            'file_source' => $file->file_source,
            'disk' => $disk,
            'storage_path' => $storagePath,
            'file_exists' => Storage::disk($disk)->exists($storagePath),
        ]);

        // Ensure the path exists in storage
        if (!Storage::disk($disk)->exists($storagePath)) {
            $root = config("filesystems.disks.{$disk}.root");
            abort(404, "File not found. Disk: {$disk}. Looking for: {$storagePath}. Root: {$root}.");
        }

        try {
            // Get file content from the appropriate disk
            $content = Storage::disk($disk)->get($storagePath);
        } catch (\Exception $e) {
            Log::error('File viewer: Error reading file', [
                'publication_id' => $publication,
                'filename' => $decodedFilename,
                'disk' => $disk,
                'storage_path' => $storagePath,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error reading file');
        }

        // Determine mime type
        $mimeType = $file->mime_type ?? 'application/octet-stream';

        // Return response with proper headers for inline viewing
        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filename) . '"')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Detect whether a path is absolute on either Unix (starts with /) or Windows (starts with drive letter e.g. C:\ or C:/)
     */
    private function isAbsolutePath(string $path): bool
    {
        // Unix absolute path
        if (str_starts_with($path, '/')) {
            return true;
        }
        // Windows absolute path: letter followed by colon and slash/backslash (e.g. C:\ or D:/)
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return true;
        }
        return false;
    }
}
