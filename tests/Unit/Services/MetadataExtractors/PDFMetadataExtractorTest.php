<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MetadataExtractors;

use App\Services\MetadataExtractors\Extractors\PDFMetadataExtractor;
use PHPUnit\Framework\TestCase;

class PDFMetadataExtractorTest extends TestCase
{
    private PDFMetadataExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new PDFMetadataExtractor();
    }

    public function test_extract_from_nonexistent_file_returns_empty_metadata(): void
    {
        $metadata = $this->extractor->extract('/nonexistent/file.pdf');

        $this->assertTrue($metadata->isEmpty());
        $this->assertNull($metadata->getTitle());
        $this->assertEmpty($metadata->getAuthors());
    }

    public function test_extract_from_filename_fallback(): void
    {
        // Create a temporary PDF file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test');

        try {
            $metadata = $this->extractor->extract($tmpFile);

            // Should extract title from filename
            $this->assertNotNull($metadata->getTitle());
            $this->assertStringContainsString('test_', $metadata->getTitle());
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_extract_isbn_from_text(): void
    {
        // Test ISBN extraction (this would need a real PDF with ISBN)
        // For now, just verify the pattern works
        $isbn = $this->extractIsbnViaReflection('ISBN-13: 978-3-16-148410-0');
        $this->assertNotNull($isbn);
    }

    private function extractIsbnViaReflection(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        // Use the protected method via reflection
        $reflection = new \ReflectionClass($this->extractor);
        $method = $reflection->getMethod('extractIsbn');
        $method->setAccessible(true);

        return $method->invoke($this->extractor, $text);
    }
}
