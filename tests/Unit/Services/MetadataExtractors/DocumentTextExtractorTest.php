<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MetadataExtractors;

use App\Services\MetadataExtractors\DocumentTextExtractor;
use Tests\TestCase;

class DocumentTextExtractorTest extends TestCase
{
    private DocumentTextExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new DocumentTextExtractor;
    }

    public function test_extract_from_nonexistent_file_returns_empty_string(): void
    {
        $text = $this->extractor->extractText('/nonexistent/file.pdf');

        $this->assertEmpty($text);
    }

    public function test_extract_from_unsupported_format_returns_empty_string(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.xyz';
        file_put_contents($tmpFile, 'test content');

        try {
            $text = $this->extractor->extractText($tmpFile);
            $this->assertEmpty($text);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_extract_from_txt_file(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.txt';
        $content = "This is a test document.\nWith multiple lines.\nFor testing purposes.";
        file_put_contents($tmpFile, $content);

        try {
            $text = $this->extractor->extractText($tmpFile);

            $this->assertNotEmpty($text);
            $this->assertStringContainsString('This is a test document', $text);
            $this->assertStringContainsString('multiple lines', $text);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_extract_respects_max_chars_limit(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.txt';
        $content = str_repeat('Lorem ipsum dolor sit amet. ', 500);
        file_put_contents($tmpFile, $content);

        try {
            $text = $this->extractor->extractText($tmpFile, 100);

            $this->assertLessThanOrEqual(100, strlen($text));
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_supports_file_returns_true_for_supported_formats(): void
    {
        $this->assertTrue($this->extractor->supportsFile('/path/to/file.pdf'));
        $this->assertTrue($this->extractor->supportsFile('/path/to/file.docx'));
        $this->assertTrue($this->extractor->supportsFile('/path/to/file.epub'));
        $this->assertTrue($this->extractor->supportsFile('/path/to/file.fb2'));
        $this->assertTrue($this->extractor->supportsFile('/path/to/file.txt'));
    }

    public function test_supports_file_returns_false_for_unsupported_formats(): void
    {
        $this->assertFalse($this->extractor->supportsFile('/path/to/file.jpg'));
        $this->assertFalse($this->extractor->supportsFile('/path/to/file.mp3'));
        $this->assertFalse($this->extractor->supportsFile('/path/to/file.xyz'));
    }

    public function test_normalizes_text_removes_excessive_whitespace(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.txt';
        $content = "Line one.\n\n\n\n\n\nLine two.\n\n\n\n\n\n\nLine three.";
        file_put_contents($tmpFile, $content);

        try {
            $text = $this->extractor->extractText($tmpFile);

            // Should not have more than 2 consecutive newlines
            $this->assertDoesNotMatchRegularExpression('/\n{3,}/', $text);
            $this->assertStringContainsString('Line one', $text);
            $this->assertStringContainsString('Line two', $text);
            $this->assertStringContainsString('Line three', $text);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_handles_empty_file(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.txt';
        file_put_contents($tmpFile, '');

        try {
            $text = $this->extractor->extractText($tmpFile);
            $this->assertEmpty($text);
        } finally {
            unlink($tmpFile);
        }
    }
}
