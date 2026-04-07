<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileMetadata extends Model
{
    use HasFactory;

    protected $table = 'file_metadatas';

    protected $fillable = [
        'publication_id',
        'file_name',
        'status',
        'extracted_data',
        'extraction_method',
        'confidence_scores',
        'error_message',
        'extracted_at',
        'confirmed_at',
        'rejected_at',
    ];

    protected $casts = [
        'extracted_data' => 'json',
        'confidence_scores' => 'json',
        'extracted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the publication this metadata belongs to.
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id', 'id_publication');
    }

    /**
     * Get title from extracted data.
     */
    public function getTitle(): ?string
    {
        $data = $this->extracted_data['title'] ?? null;
        return is_array($data) ? ($data['value'] ?? null) : $data;
    }

    /**
     * Get authors from extracted data.
     *
     * @return array<string>
     */
    public function getAuthors(): array
    {
        if (! isset($this->extracted_data['authors']) || ! is_array($this->extracted_data['authors'])) {
            return [];
        }

        return array_map(fn ($author) => is_array($author) ? ($author['value'] ?? '') : $author, $this->extracted_data['authors']);
    }

    /**
     * Get publication year from extracted data.
     */
    public function getPublicationYear(): ?int
    {
        $data = $this->extracted_data['publication_year'] ?? null;
        if (is_array($data)) {
            return isset($data['value']) ? (int) $data['value'] : null;
        }
        return $data !== null ? (int) $data : null;
    }

    /**
     * Get publisher from extracted data.
     */
    public function getPublisher(): ?string
    {
        $data = $this->extracted_data['publisher'] ?? null;
        return is_array($data) ? ($data['value'] ?? null) : $data;
    }



    /**
     * Get genres from extracted data.
     *
     * @return array<string>
     */
    public function getGenres(): array
    {
        if (! isset($this->extracted_data['genres']) || ! is_array($this->extracted_data['genres'])) {
            return [];
        }

        return array_map(fn ($genre) => is_array($genre) ? ($genre['value'] ?? '') : $genre, $this->extracted_data['genres']);
    }

    /**
     * Get themes from extracted data.
     *
     * @return array<string>
     */
    public function getThemes(): array
    {
        if (! isset($this->extracted_data['themes']) || ! is_array($this->extracted_data['themes'])) {
            return [];
        }

        return array_map(fn ($theme) => is_array($theme) ? ($theme['value'] ?? '') : $theme, $this->extracted_data['themes']);
    }

    /**
     * Get fields above confidence threshold.
     *
     * @param  float  $threshold  Minimum confidence (0.0-1.0)
     * @return array<string, mixed>
     */
    public function getHighestConfidenceFields(float $threshold = 0.6): array
    {
        return $this->extracted_data ?? [];
    }

    /**
     * Mark extraction as rejected.
     */
    public function reject(): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    /**
     * Mark extraction as confirmed.
     */
    public function confirm(): bool
    {
        return $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Scope: Get pending extractions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get processed extractions (ready for review).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope: Get confirmed extractions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Get failed extractions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope: Get by extraction method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('extraction_method', $method);
    }
}
