<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

use App\Services\MetadataExtractors\Extractors\DJVUMetadataExtractor;
use App\Services\MetadataExtractors\Extractors\DOCMetadataExtractor;
use App\Services\MetadataExtractors\Extractors\DOCXMetadataExtractor;
use App\Services\MetadataExtractors\Extractors\EPUBMetadataExtractor;
use App\Services\MetadataExtractors\Extractors\FB2MetadataExtractor;
use App\Services\MetadataExtractors\Extractors\PDFMetadataExtractor;
use App\Services\MetadataExtractors\Extractors\TXTMetadataExtractor;
use Illuminate\Support\Facades\Log;

class MetadataExtractorFactory
{
    /**
     * Create appropriate metadata extractor based on file MIME type.
     *
     * @param  string  $filePath  Absolute path to file
     * @param  string  $contentType  MIME type or file extension
     *
     * @throws \InvalidArgumentException If format is not supported
     */
    public static function create(string $filePath, string $contentType): MetadataExtractorInterface
    {
        // Normalize content type/extension
        $contentType = strtolower(trim($contentType));

        // Handle file extension
        if (! str_contains($contentType, '/')) {
            $contentType = self::extensionToMimeType($contentType);
        }

        return match ($contentType) {
            'application/pdf' => new PDFMetadataExtractor,
            'application/epub+zip', 'application/epub' => new EPUBMetadataExtractor,
            'text/plain' => new TXTMetadataExtractor,
            'application/msword' => new DOCMetadataExtractor,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => new DOCXMetadataExtractor,
            'application/x-fictionbook', 'text/xml' => self::detectFB2OrXml($filePath),
            'image/vnd.djvu' => new DJVUMetadataExtractor,
            default => throw new \InvalidArgumentException("Unsupported content type: {$contentType}"),
        };
    }

    /**
     * Convert file extension to MIME type.
     */
    private static function extensionToMimeType(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'application/pdf',
            'epub' => 'application/epub+zip',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'fb2' => 'application/x-fictionbook',
            'xml' => 'text/xml',
            'djvu' => 'image/vnd.djvu',
            default => 'application/octet-stream',
        };
    }

    /**
     * Detect if XML file is FB2 (FictionBook) format.
     */
    private static function detectFB2OrXml(string $filePath): MetadataExtractorInterface
    {
        try {
            $content = file_get_contents($filePath, false, null, 0, 1000);
            if ($content && str_contains($content, '<FictionBook')) {
                return new FB2MetadataExtractor;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to detect FB2 format', ['file' => $filePath, 'error' => $e->getMessage()]);
        }

        // Default to FB2 for .fb2 extension, fallback to TXT for generic XML
        return str_ends_with($filePath, '.fb2') ? new FB2MetadataExtractor : new TXTMetadataExtractor;
    }

    /**
     * Get list of supported MIME types.
     *
     * @return string[]
     */
    public static function supportedMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/epub+zip',
            'application/epub',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-fictionbook',
            'text/xml',
            'image/vnd.djvu',
        ];
    }

    /**
     * Get list of supported extensions.
     *
     * @return string[]
     */
    public static function supportedExtensions(): array
    {
        return ['pdf', 'epub', 'txt', 'doc', 'docx', 'fb2', 'xml', 'djvu'];
    }

    /**
     * Check if content type is supported.
     *
     * @param  string  $contentType  MIME type or extension
     */
    public static function isSupported(string $contentType): bool
    {
        $contentType = strtolower(trim($contentType));

        // Check as extension
        if (! str_contains($contentType, '/')) {
            return in_array($contentType, self::supportedExtensions());
        }

        return in_array($contentType, self::supportedMimeTypes());
    }
}
