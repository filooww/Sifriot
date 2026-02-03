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
                'author_count' => count($metadata->getAuthors()),
                'has_year' => (bool) $metadata->getPublicationYear(),
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
        return <<<PROMPT
Extract bibliographic metadata from this document. If title/author not explicit, INFER from content.
Prioritize returning values in **Russian** where applicable (content_type, themes, section, genres).

Return JSON matching this schema:
{
  "title": "string (required)",
  "authors": ["strings"],
  "publication_year": int|null,
  "publisher": "string|null",
  "issuer": "string|null (issuing organization if different from publisher)",
  "content_type": "string (must be one of: Книги, Журналы, Статьи, Другое)",
  "genres": ["strings"],
  "themes": ["strings (abstract content themes/topics)"],
  "section": "string (best match from list below)",
  "description": "string (2-3 sentence summary)"
}

Valid Sections (pick one best fit):
- Избранное > Выбор редакции
- Избранное > Новые поступления
- Коллекции > По теме
- Коллекции > По автору
- Обзор > Недавние
- Обзор > Популярные

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

            // Set issuer
            if (! empty($data['issuer'])) {
                $metadata->setIssuer($data['issuer']);
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
            }

            $this->log('debug', 'Parsed Gemini response', [
                'fields' => array_keys($data),
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
