<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiMetadataExtractorService
{
    private const API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    private string $apiKey;

    private string $model;

    private int $timeout;

    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->timeout = config('services.gemini.timeout', 30);
        $this->maxRetries = config('services.gemini.max_retries', 3);
    }

    /**
     * Check if Gemini API is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Extract metadata from text content using Gemini LLM.
     *
     * @param  string  $textContent  The document text to analyze
     * @return ExtractedMetadata Extracted metadata with confidence scores
     *
     * @throws GeminiConfigurationException If API key is not configured
     * @throws GeminiTimeoutException If API call times out after retries
     */
    public function extract(string $textContent): ExtractedMetadata
    {
        if (! $this->isConfigured()) {
            throw new GeminiConfigurationException('Gemini API key is not configured');
        }

        if (empty(trim($textContent))) {
            $this->log('warning', 'Empty text content provided for extraction');

            return new ExtractedMetadata;
        }

        $prompt = $this->buildPrompt($textContent);

        $this->log('info', 'Starting Gemini extraction', [
            'text_length' => strlen($textContent),
            'model' => $this->model,
        ]);

        try {
            $response = $this->callApi($prompt);
            $metadata = $this->parseResponse($response);

            $this->log('info', 'Gemini extraction successful', [
                'has_title' => (bool) $metadata->getTitle(),
                'title' => $metadata->getTitle(),
                'author_count' => count($metadata->getAuthors()),
                'authors' => $metadata->getAuthors(),
                'has_year' => (bool) $metadata->getPublicationYear(),
                'year' => $metadata->getPublicationYear(),
                'has_publisher' => (bool) $metadata->getPublisher(),
                'publisher' => $metadata->getPublisher(),
                'has_description' => (bool) $metadata->getDescription(),
                'description_length' => strlen($metadata->getDescription() ?? ''),
                'content_type' => $metadata->getContentType(),
                'genre_count' => count($metadata->getGenres()),
                'theme_count' => count($metadata->getThemes()),
                'section' => $metadata->getSection(),
            ]);

            return $metadata;
        } catch (GeminiTimeoutException $e) {
            $this->log('error', 'Gemini API timeout', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'Gemini extraction failed', ['error' => $e->getMessage()]);

            return new ExtractedMetadata;
        }
    }

    /**
     * Build the extraction prompt.
     */
    /**
     * Build the extraction prompt.
     */
    private function buildPrompt(string $textContent): string
    {
        $sectionsList = \App\Models\Section::with('parent')->get()->map(function($s) {
            $name = $s->name_ru ?: $s->name_en;
            if ($s->parent) {
                $parentName = $s->parent->name_ru ?: $s->parent->name_en;
                return "- {$parentName} > {$name}";
            }
            return "- {$name}";
        })->implode("\n");

        if (empty(trim($sectionsList))) {
            $sectionsList = "- (No sections available)";
        }

        return <<<PROMPT
Extract comprehensive bibliographic metadata from this document. If title/author not explicit, INFER from content.
Prioritize returning values in **Russian** where applicable (content_type, themes, section, genres).

**IMPORTANT:**
- For BOOKS: Extract book title, author(s), publisher, publication year
- For MAGAZINES/JOURNALS: Extract magazine/journal name as title, editor or no author, publisher (issuing organization), full issue date
- For ARTICLES: Extract article title, author(s), source publication, publication date

Return JSON matching this schema:
{
  "title": "string (required - book title, magazine name, or article title)",
  "authors": ["strings (authors for books/articles, editors for magazines, empty array if none found)"],
  "publication_year": int|null (year of publication - REQUIRED if found in document)",
  "publisher": "string|null (publisher name for books, issuing organization for magazines/journals - REQUIRED if found)",
  "content_type": "string (must be one of: Книги, Журналы, Статьи, Другое)",
  "genres": ["strings (literary genres for books, subject areas for magazines/journals)"],
  "themes": ["strings (abstract content themes, topics, subjects covered in detail)"],
  "section": "string (best match from list below based on content/topic)",
  "description": "string (comprehensive 3-5 sentence description covering: what the document is about, main topics covered, target audience, and key features - REQUIRED)"
}

**Extraction Guidelines:**
- **Title**: Use the main title shown prominently. For magazines, use the magazine name.
- **Authors**: Extract individual author names for books and articles. For magazines, extract editors if available, otherwise use empty array.
- **Publication Year**: Extract the specific year from publication information, copyright page, or issue date. This is REQUIRED if present.
- **Publisher**: Extract the publishing company name for books, or the issuing organization/publisher for magazines and journals. This is REQUIRED if present.
- **Content Type**: Choose based on document format:
  - "Книги" for books, textbooks, monographs
  - "Журналы" for magazines, journals, periodicals
  - "Статьи" for individual articles, papers
  - "Другое" for other document types
- **Description**: Write a detailed 3-5 sentence description covering:
  1. What the document is (book/magazine/article about what subject)
  2. Main topics, themes, or subject matter covered
  3. Target audience or field of study
  4. Notable features, approach, or perspective
  5. Any distinctive characteristics or special focus

Valid Sections (pick one best fit based on content/topic):
{$sectionsList}

Document content:
---
{$textContent}
---

JSON only, no markdown:
PROMPT;
    }

    /**
     * Call the Gemini API with retry logic.
     *
     * @throws GeminiTimeoutException
     */
    private function callApi(string $prompt): array
    {
        $url = self::API_BASE_URL."/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topP' => 0.95,
                'topK' => 40,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
            ],
        ];

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;
            $delay = $this->calculateBackoff($attempt);

            try {
                $this->log('debug', "API call attempt {$attempt}/{$this->maxRetries}");

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $this->apiKey,
                    ])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $this->log('debug', 'API call successful', [
                        'status' => $response->status(),
                    ]);

                    return $response->json();
                }

                // Handle rate limiting
                if ($response->status() === 429) {
                    $this->log('warning', 'Rate limited by Gemini API', [
                        'attempt' => $attempt,
                        'retry_delay' => $delay,
                    ]);

                    if ($attempt < $this->maxRetries) {
                        sleep($delay);

                        continue;
                    }
                }

                // Handle other errors
                $this->log('error', 'Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $lastException = new \Exception("API error: {$response->status()}");

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $this->log('warning', 'Connection timeout', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                $lastException = new GeminiTimeoutException(
                    "Connection timeout after {$attempt} attempts: ".$e->getMessage()
                );

                if ($attempt < $this->maxRetries) {
                    sleep($delay);

                    continue;
                }
            }
        }

        throw $lastException ?? new GeminiTimeoutException('Max retries exceeded');
    }

    /**
     * Calculate exponential backoff delay.
     */
    private function calculateBackoff(int $attempt): int
    {
        return min(pow(2, $attempt), 30); // Max 30 seconds
    }

    /**
     * Parse Gemini API response into ExtractedMetadata.
     */
    private function parseResponse(array $response): ExtractedMetadata
    {
        $metadata = new ExtractedMetadata;

        try {
            // Extract text from response
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $text) {
                $this->log('warning', 'Empty response from Gemini');

                return $metadata;
            }

            // Clean the response (remove markdown code blocks if present)
            $text = $this->cleanJsonResponse($text);

            // Parse JSON
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($data)) {
                $this->log('warning', 'Invalid JSON structure from Gemini');

                return new ExtractedMetadata;
            }

            // Set basic fields
            if (! empty($data['title'])) {
                $metadata->setTitle($data['title']);
            }

            if (! empty($data['authors']) && is_array($data['authors'])) {
                foreach ($data['authors'] as $author) {
                    if (is_string($author) && ! empty(trim($author))) {
                        $metadata->addAuthor(trim($author));
                    }
                }
            }

            if (! empty($data['publication_year'])) {
                $metadata->setPublicationYear((int) $data['publication_year']);
            }

            if (! empty($data['publisher'])) {
                $metadata->setPublisher($data['publisher']);
            }



            // Set content type
            if (! empty($data['content_type'])) {
                $metadata->setContentType($data['content_type']);
            }

            // Set genres
            if (! empty($data['genres']) && is_array($data['genres'])) {
                foreach ($data['genres'] as $genre) {
                    if (is_string($genre) && ! empty(trim($genre))) {
                        $metadata->addGenre(trim($genre));
                    }
                }
            }

            // Set themes
            if (! empty($data['themes']) && is_array($data['themes'])) {
                foreach ($data['themes'] as $theme) {
                    if (is_string($theme) && ! empty(trim($theme))) {
                        $metadata->addTheme(trim($theme));
                    }
                }
            }

            // Set section
            if (! empty($data['section'])) {
                $metadata->setSection($data['section']);
            }

            // Set description
            if (! empty($data['description'])) {
                $metadata->setDescription($data['description']);
            } else {
                $this->log('warning', 'No description extracted from AI response', [
                    'title' => $data['title'] ?? 'unknown',
                ]);
            }

            // Validate critical fields
            if (empty($data['title'])) {
                $this->log('warning', 'No title extracted - this is a critical field');
            }

            if (empty($data['publication_year'])) {
                $this->log('warning', 'No publication year extracted - this field is required if present in document');
            }

            if (empty($data['publisher'])) {
                $this->log('warning', 'No publisher/issuer extracted - this field is required if present in document');
            }

            $this->log('debug', 'Parsed Gemini response', [
                'fields' => array_keys($data),
                'data' => $data,
            ]);

        } catch (\JsonException $e) {
            $this->log('warning', 'Failed to parse Gemini JSON response', [
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->log('warning', 'Unexpected error parsing response', [
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Clean JSON response from markdown formatting.
     */
    private function cleanJsonResponse(string $text): string
    {
        // Remove markdown code blocks
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);

        return trim($text);
    }

    /**
     * Log activity to folder_scan channel.
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('folder_scan')->{$level}("[GeminiExtractor] {$message}", $context);
    }
}
