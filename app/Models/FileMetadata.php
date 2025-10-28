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
        'file_id',
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
     * Relationship: Belongs to File.
     *
     * @return BelongsTo
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * Get title from extracted data.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->extracted_data['title']['value'] ?? null;
    }

    /**
     * Get authors from extracted data.
     *
     * @return array<string>
     */
    public function getAuthors(): array
    {
        if (!isset($this->extracted_data['authors']) || !is_array($this->extracted_data['authors'])) {
            return [];
        }

        return array_map(fn ($author) => $author['value'] ?? '', $this->extracted_data['authors']);
    }

    /**
     * Get publication year from extracted data.
     *
     * @return int|null
     */
    public function getPublicationYear(): ?int
    {
        return isset($this->extracted_data['publication_year']['value'])
            ? (int) $this->extracted_data['publication_year']['value']
            : null;
    }

    /**
     * Get publisher from extracted data.
     *
     * @return string|null
     */
    public function getPublisher(): ?string
    {
        return $this->extracted_data['publisher']['value'] ?? null;
    }

    /**
     * Get ISBN from extracted data.
     *
     * @return string|null
     */
    public function getIsbn(): ?string
    {
        return $this->extracted_data['isbn']['value'] ?? null;
    }

    /**
     * Get DOI from extracted data.
     *
     * @return string|null
     */
    public function getDoi(): ?string
    {
        return $this->extracted_data['doi']['value'] ?? null;
    }

    /**
     * Get fields above confidence threshold.
     *
     * @param float $threshold Minimum confidence (0.0-1.0)
     * @return array<string, mixed>
     */
    public function getHighestConfidenceFields(float $threshold = 0.6): array
    {
        $result = [];

        if (isset($this->extracted_data['title'])) {
            $confidence = $this->extracted_data['title']['confidence'] ?? 0;
            if ($confidence >= $threshold) {
                $result['title'] = $this->extracted_data['title'];
            }
        }

        if (isset($this->extracted_data['authors'])) {
            $result['authors'] = array_filter(
                $this->extracted_data['authors'],
                fn ($author) => ($author['confidence'] ?? 0) >= $threshold
            );
        }

        if (isset($this->extracted_data['publication_year'])) {
            $confidence = $this->extracted_data['publication_year']['confidence'] ?? 0;
            if ($confidence >= $threshold) {
                $result['publication_year'] = $this->extracted_data['publication_year'];
            }
        }

        if (isset($this->extracted_data['publisher'])) {
            $confidence = $this->extracted_data['publisher']['confidence'] ?? 0;
            if ($confidence >= $threshold) {
                $result['publisher'] = $this->extracted_data['publisher'];
            }
        }

        if (isset($this->extracted_data['isbn'])) {
            $confidence = $this->extracted_data['isbn']['confidence'] ?? 0;
            if ($confidence >= $threshold) {
                $result['isbn'] = $this->extracted_data['isbn'];
            }
        }

        if (isset($this->extracted_data['doi'])) {
            $confidence = $this->extracted_data['doi']['confidence'] ?? 0;
            if ($confidence >= $threshold) {
                $result['doi'] = $this->extracted_data['doi'];
            }
        }

        return $result;
    }

    /**
     * Mark extraction as rejected.
     *
     * @return bool
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
     *
     * @return bool
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get processed extractions (ready for review).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope: Get confirmed extractions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Get failed extractions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $status
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('extraction_method', $method);
    }
}
