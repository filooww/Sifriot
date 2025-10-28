<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

class ExtractedMetadata
{
    /**
     * @var array<string, mixed> Metadata field with confidence score
     */
    private array $title = [];

    /**
     * @var array<array<string, mixed>> Authors array with confidence scores
     */
    private array $authors = [];

    /**
     * @var array<string, mixed> Publication year with confidence score
     */
    private array $publication_year = [];

    /**
     * @var array<string, mixed> Publisher with confidence score
     */
    private array $publisher = [];

    /**
     * @var array<string, mixed> ISBN with confidence score
     */
    private array $isbn = [];

    /**
     * @var array<string, mixed> DOI with confidence score
     */
    private array $doi = [];

    /**
     * @var array<string, float> Overall field confidence scores (0.0-1.0)
     */
    private array $confidence_scores = [];

    /**
     * Set title with confidence score.
     *
     * @param string|null $value
     * @param float $confidence (0.0-1.0)
     */
    public function setTitle(?string $value, float $confidence = 0.5): self
    {
        if ($value !== null) {
            $this->title = [
                'value' => $value,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Add author with confidence score.
     *
     * @param string $name Author name
     * @param float $confidence (0.0-1.0)
     */
    public function addAuthor(string $name, float $confidence = 0.5): self
    {
        if (!empty($name)) {
            $this->authors[] = [
                'value' => $name,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Set publication year with confidence score.
     *
     * @param int|string|null $value
     * @param float $confidence (0.0-1.0)
     */
    public function setPublicationYear($value, float $confidence = 0.5): self
    {
        if ($value !== null) {
            $this->publication_year = [
                'value' => (int) $value,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Set publisher with confidence score.
     *
     * @param string|null $value
     * @param float $confidence (0.0-1.0)
     */
    public function setPublisher(?string $value, float $confidence = 0.5): self
    {
        if ($value !== null) {
            $this->publisher = [
                'value' => $value,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Set ISBN with confidence score.
     *
     * @param string|null $value
     * @param float $confidence (0.0-1.0)
     */
    public function setIsbn(?string $value, float $confidence = 0.5): self
    {
        if ($value !== null) {
            $this->isbn = [
                'value' => $value,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Set DOI with confidence score.
     *
     * @param string|null $value
     * @param float $confidence (0.0-1.0)
     */
    public function setDoi(?string $value, float $confidence = 0.5): self
    {
        if ($value !== null) {
            $this->doi = [
                'value' => $value,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        }
        return $this;
    }

    /**
     * Get title value.
     */
    public function getTitle(): ?string
    {
        return $this->title['value'] ?? null;
    }

    /**
     * Get all authors as array of names.
     *
     * @return string[]
     */
    public function getAuthors(): array
    {
        return array_map(fn ($author) => $author['value'], $this->authors);
    }

    /**
     * Get publication year.
     */
    public function getPublicationYear(): ?int
    {
        return isset($this->publication_year['value']) ? (int) $this->publication_year['value'] : null;
    }

    /**
     * Get publisher.
     */
    public function getPublisher(): ?string
    {
        return $this->publisher['value'] ?? null;
    }

    /**
     * Get ISBN.
     */
    public function getIsbn(): ?string
    {
        return $this->isbn['value'] ?? null;
    }

    /**
     * Get DOI.
     */
    public function getDoi(): ?string
    {
        return $this->doi['value'] ?? null;
    }

    /**
     * Get highest confidence fields (above threshold).
     *
     * @param float $threshold Minimum confidence (0.0-1.0)
     * @return array<string, mixed>
     */
    public function getHighestConfidenceFields(float $threshold = 0.6): array
    {
        $result = [];

        if (isset($this->title['confidence']) && $this->title['confidence'] >= $threshold) {
            $result['title'] = $this->title;
        }

        if (!empty($this->authors)) {
            $result['authors'] = array_filter(
                $this->authors,
                fn ($author) => $author['confidence'] >= $threshold
            );
        }

        if (isset($this->publication_year['confidence']) && $this->publication_year['confidence'] >= $threshold) {
            $result['publication_year'] = $this->publication_year;
        }

        if (isset($this->publisher['confidence']) && $this->publisher['confidence'] >= $threshold) {
            $result['publisher'] = $this->publisher;
        }

        if (isset($this->isbn['confidence']) && $this->isbn['confidence'] >= $threshold) {
            $result['isbn'] = $this->isbn;
        }

        if (isset($this->doi['confidence']) && $this->doi['confidence'] >= $threshold) {
            $result['doi'] = $this->doi;
        }

        return $result;
    }

    /**
     * Check if extraction is empty (no data extracted).
     */
    public function isEmpty(): bool
    {
        return empty($this->title)
            && empty($this->authors)
            && empty($this->publication_year)
            && empty($this->publisher)
            && empty($this->isbn)
            && empty($this->doi);
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'authors' => $this->authors,
            'publication_year' => $this->publication_year,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'doi' => $this->doi,
        ];
    }

    /**
     * Get confidence scores for all fields.
     *
     * @return array<string, float>
     */
    public function getConfidenceScores(): array
    {
        return [
            'title' => $this->title['confidence'] ?? 0.0,
            'authors' => $this->authors ? (array_sum(array_column($this->authors, 'confidence')) / count($this->authors)) : 0.0,
            'publication_year' => $this->publication_year['confidence'] ?? 0.0,
            'publisher' => $this->publisher['confidence'] ?? 0.0,
            'isbn' => $this->isbn['confidence'] ?? 0.0,
            'doi' => $this->doi['confidence'] ?? 0.0,
        ];
    }
}
