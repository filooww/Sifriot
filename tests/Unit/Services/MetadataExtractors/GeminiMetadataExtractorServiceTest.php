<?php

declare(strict_types=1);

namespace Tests\Unit\Services\MetadataExtractors;

use App\Services\MetadataExtractors\GeminiConfigurationException;
use App\Services\MetadataExtractors\GeminiMetadataExtractorService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiMetadataExtractorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up default Gemini config for tests
        Config::set('services.gemini', [
            'api_key' => 'test-api-key',
            'model' => 'gemini-1.5-flash',
            'timeout' => 30,
            'max_retries' => 1,
        ]);
    }

    public function test_is_configured_returns_true_when_api_key_set(): void
    {
        $service = new GeminiMetadataExtractorService;
        $this->assertTrue($service->isConfigured());
    }

    public function test_is_configured_returns_false_when_api_key_empty(): void
    {
        Config::set('services.gemini.api_key', '');
        $service = new GeminiMetadataExtractorService;

        $this->assertFalse($service->isConfigured());
    }

    public function test_extract_throws_exception_when_not_configured(): void
    {
        Config::set('services.gemini.api_key', '');
        $service = new GeminiMetadataExtractorService;

        $this->expectException(GeminiConfigurationException::class);
        $service->extract('Sample text content');
    }

    public function test_extract_returns_empty_metadata_for_empty_text(): void
    {
        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('');

        $this->assertTrue($metadata->isEmpty());
    }

    public function test_extract_parses_successful_api_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Test Book Title',
                                        'authors' => ['John Doe', 'Jane Smith'],
                                        'publication_year' => 2024,
                                        'publisher' => 'Test Publisher',
                                        'issuer' => 'Test Org',
                                        'genres' => ['Fiction', 'Drama'],
                                        'themes' => ['Adventure'],
                                        'content_type' => 'Books',
                                        'section' => 'New Arrivals',
                                        'description' => 'A test description',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('This is a sample book about testing.');

        $this->assertEquals('Test Book Title', $metadata->getTitle());
        $this->assertEquals(['John Doe', 'Jane Smith'], $metadata->getAuthors());
        $this->assertEquals(2024, $metadata->getPublicationYear());
        $this->assertEquals('Test Publisher', $metadata->getPublisher());
        $this->assertEquals('Test Org', $metadata->getIssuer());
        $this->assertEquals(['Fiction', 'Drama'], $metadata->getGenres());
        $this->assertEquals(['Adventure'], $metadata->getThemes());
        $this->assertEquals('Books', $metadata->getContentType());
        $this->assertEquals('New Arrivals', $metadata->getSection());
        $this->assertEquals('A test description', $metadata->getDescription());
    }

    public function test_extract_handles_json_in_markdown_code_block(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => "```json\n" . json_encode([
                                        'title' => 'Markdown Wrapped Title',
                                        'authors' => ['Author Name'],
                                        'publication_year' => 2023,
                                        'publisher' => null,
                                        'genres' => [],
                                    ]) . "\n```",
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Sample text');

        $this->assertEquals('Markdown Wrapped Title', $metadata->getTitle());
        $this->assertEquals(['Author Name'], $metadata->getAuthors());
    }

    public function test_extract_returns_empty_metadata_on_api_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Sample text');

        $this->assertTrue($metadata->isEmpty());
    }

    public function test_extract_handles_invalid_json_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'This is not valid JSON',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Sample text');

        $this->assertTrue($metadata->isEmpty());
    }

    public function test_extract_filters_invalid_publication_year(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Valid Title',
                                        'authors' => [],
                                        'publication_year' => 999, // Invalid year
                                        'publisher' => null,
                                        'genres' => [],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Sample text');

        $this->assertEquals('Valid Title', $metadata->getTitle());
        $this->assertNull($metadata->getPublicationYear());
    }

    public function test_extract_handles_future_year(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Future Book',
                                        'authors' => [],
                                        'publication_year' => 3000, // Future year
                                        'publisher' => null,
                                        'genres' => [],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Sample text');

        $this->assertNull($metadata->getPublicationYear());
    }

    public function test_extract_handles_multilingual_content(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Русская книга',
                                        'authors' => ['Иван Иванов'],
                                        'publication_year' => 2020,
                                        'publisher' => 'Издательство',
                                        'genres' => ['Художественная литература'],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GeminiMetadataExtractorService;
        $metadata = $service->extract('Пример русского текста');

        $this->assertEquals('Русская книга', $metadata->getTitle());
        $this->assertEquals(['Иван Иванов'], $metadata->getAuthors());
        $this->assertEquals('Издательство', $metadata->getPublisher());
    }
}
