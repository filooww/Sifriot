<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'publications';

    protected $primaryKey = 'id_publication';

    protected $fillable = [
        'title',
        'title_low',
        'id_publishing',
        'id_part',
        'issue_year',
        'id_issue_type',
        'id_magazine',
        'upload_date',
        'status',
        'original_folder_path',
        'content_type_id',
        'actuality',
        'id_theme_set',
        'id_author_set',
        'add_int',
        'add_char',
        'word_count',
        'extracted_author_names',
        'extracted_publication_year',
        'extracted_publisher',
        'extracted_isbn',
        'extracted_doi',
        'metadata_source',
        'metadata_confidence_avg',
        'metadata_confirmed_at',
        'metadata_previous_values',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'actuality' => 'integer',
        'add_int' => 'integer',
        'word_count' => 'integer',
        'status' => 'string',
        'extracted_author_names' => 'array',
        'extracted_publication_year' => 'integer',
        'metadata_confidence_avg' => 'decimal:2',
        'metadata_confirmed_at' => 'datetime',
        'metadata_previous_values' => 'array',
    ];

    protected $appends = [
        'formatted_upload_date',
        'total_file_size',
    ];

    // Relationships
    public function publishing(): BelongsTo
    {
        return $this->belongsTo(Publishing::class, 'id_publishing', 'id_publishing');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'id_part', 'id_part');
    }

    public function issueType(): BelongsTo
    {
        return $this->belongsTo(IssueType::class, 'id_issue_type', 'id_issue_type');
    }

    public function magazine(): BelongsTo
    {
        return $this->belongsTo(Magazine::class, 'id_magazine', 'id_magazine');
    }

    public function themeSet(): BelongsTo
    {
        return $this->belongsTo(ThemeSet::class, 'id_theme_set', 'id_theme_set');
    }

    public function authorGroup(): BelongsTo
    {
        return $this->belongsTo(AuthorGroup::class, 'id_author_set', 'id_author_group');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'id_publication', 'id_publication');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            Author::class,
            'author_publication',
            'id_publication',
            'id_author',
            'id_publication',
            'id_author'
        )->withPivot('order')->withTimestamps()->orderByPivot('order');
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(
            Theme::class,
            'publication_theme',
            'id_publication',
            'id_theme',
            'id_publication',
            'id_theme'
        )->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_publication',
            'publication_id',
            'category_id',
            'id_publication',
            'id'
        )->withTimestamps();
    }

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    public function fileMetadata(): HasMany
    {
        return $this->hasMany(FileMetadata::class, 'file_id', 'id_publication');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Accessors
    protected function formattedUploadDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->upload_date?->translatedFormat('d F Y')
        );
    }

    protected function totalFileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->files()->sum('file_size_bytes') ?? 0
        );
    }

    // Metadata Accessors
    public function getExtractedAuthorsAttribute(): array
    {
        if (is_array($this->extracted_author_names)) {
            return $this->extracted_author_names;
        }

        if (is_string($this->extracted_author_names)) {
            return json_decode($this->extracted_author_names, true) ?? [];
        }

        return [];
    }

    public function getMetadataAsArray(): array
    {
        return [
            'authors' => $this->extracted_author_names,
            'publication_year' => $this->extracted_publication_year,
            'publisher' => $this->extracted_publisher,
            'isbn' => $this->extracted_isbn,
            'doi' => $this->extracted_doi,
            'confidence_avg' => $this->metadata_confidence_avg,
            'source' => $this->metadata_source,
            'confirmed_at' => $this->metadata_confirmed_at,
        ];
    }
}
