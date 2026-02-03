<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

class ExtractedMetadata
{
    private ?string $title = null;

    private array $authors = [];

    private ?int $publication_year = null;

    private ?string $publisher = null;

    private array $genres = [];

    private ?string $content_type = null;

    private array $themes = [];

    private ?string $section = null;

    private ?string $issuer = null;

    private ?string $description = null;

    public function setTitle(?string $value): self
    {
        $this->title = $value;

        return $this;
    }

    public function addAuthor(string $name): self
    {
        if (! empty($name)) {
            $this->authors[] = $name;
        }

        return $this;
    }

    public function setPublicationYear($value): self
    {
        if ($value === null) {
            $this->publication_year = null;
            return $this;
        }

        $year = (int) $value;
        // Validation: 1000 to Current Year + 5
        if ($year >= 1000 && $year <= (int) date('Y') + 5) {
            $this->publication_year = $year;
        }

        return $this;
    }

    public function setPublisher(?string $value): self
    {
        $this->publisher = $value;

        return $this;
    }

    public function addGenre(string $name): self
    {
        if (! empty($name)) {
            $this->genres[] = $name;
        }

        return $this;
    }

    public function setContentType(?string $value): self
    {
        $this->content_type = $value;

        return $this;
    }

    public function addTheme(string $name): self
    {
        if (! empty($name)) {
            $this->themes[] = $name;
        }

        return $this;
    }

    public function setSection(?string $value): self
    {
        $this->section = $value;

        return $this;
    }

    public function setIssuer(?string $value): self
    {
        $this->issuer = $value;

        return $this;
    }

    public function setDescription(?string $value): self
    {
        $this->description = $value;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getPublicationYear(): ?int
    {
        return $this->publication_year;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    public function getThemes(): array
    {
        return $this->themes;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getHighestConfidenceFields(float $threshold = 0.6): array
    {
        // Deprecated method kept for interface compatibility if needed, but returns simple array
        return $this->toArray();
    }

    public function isEmpty(): bool
    {
        return empty($this->title)
            && empty($this->authors)
            && empty($this->publication_year)
            && empty($this->publisher)
            && empty($this->genres)
            && empty($this->content_type)
            && empty($this->themes)
            && empty($this->section)
            && empty($this->issuer)
            && empty($this->description);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'authors' => $this->authors,
            'publication_year' => $this->publication_year,
            'publisher' => $this->publisher,
            'genres' => $this->genres,
            'content_type' => $this->content_type,
            'themes' => $this->themes,
            'section' => $this->section,
            'issuer' => $this->issuer,
            'description' => $this->description,
        ];
    }

    public function getConfidenceScores(): array
    {
        return [];
    }
}
