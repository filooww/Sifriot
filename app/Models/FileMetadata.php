<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get the files associated with this metadata.
     * Returns files matching publication_id and file_name from the file_id.
     */
    public function file(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Extract publication ID from file_id (format: "123-filename.pdf")
        if (! $this->file_id) {
            return $this->hasMany(File::class, 'id_publication', 'id_publication')->whereRaw('1=0');
        }

        $parts = explode('-', $this->file_id, 2);
        $publicationId = (int) ($parts[0] ?? 0);

        if ($publicationId === 0) {
            return $this->hasMany(File::class, 'id_publication', 'id_publication')->whereRaw('1=0');
        }

        return $this->hasMany(File::class, 'id_publication', 'id_publication')
            ->where('id_publication', $publicationId)
            ->where('file_name', $this->file_name);
    }

    /**
     * Get the publication associated with this metadata.
     * Note: file_id format is "publication_id-filename.ext"
     * Since we don't have a traditional foreign key, we extract the publication ID from file_id.
     *
     * @return \App\Models\Publication|null
     */
    public function getPublication()
    {
        // Extract publication ID from file_id (format: "123-filename.pdf")
        if (! $this->file_id) {
            return null;
        }

        $parts = explode('-', $this->file_id);
        $publicationId = (int) ($parts[0] ?? 0);

        if ($publicationId === 0) {
            return null;
        }

        // Return the publication or null
        return Publication::find($publicationId);
    }

    /**
     * Magic accessor for publication property
     */
    public function __get($key)
    {
        if ($key === 'publication') {
            return $this->getPublication();
        }

        return parent::__get($key);
    }

    /**
     * Get title from extracted data.
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
        if (! isset($this->extracted_data['authors']) || ! is_array($this->extracted_data['authors'])) {
            return [];
        }

        return array_map(fn ($author) => $author['value'] ?? '', $this->extracted_data['authors']);
    }

    /**
     * Get publication year from extracted data.
     */
    public function getPublicationYear(): ?int
    {
        return isset($this->extracted_data['publication_year']['value'])
            ? (int) $this->extracted_data['publication_year']['value']
            : null;
    }

    /**
     * Get publisher from extracted data.
     */
    public function getPublisher(): ?string
    {
        return $this->extracted_data['publisher']['value'] ?? null;
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

        return array_map(fn ($genre) => $genre['value'] ?? '', $this->extracted_data['genres']);
    }

    /**
     * Get fields above confidence threshold.
     *
     * @param  float  $threshold  Minimum confidence (0.0-1.0)
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

        if (isset($this->extracted_data['genres'])) {
            $result['genres'] = array_filter(
                $this->extracted_data['genres'],
                fn ($genre) => ($genre['confidence'] ?? 0) >= $threshold
            );
        }

        return $result;
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
