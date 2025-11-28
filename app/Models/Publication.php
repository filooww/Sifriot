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
        'description',
        'word_count',
        'metadata_previous_values',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'actuality' => 'integer',
        'word_count' => 'integer',
        'status' => 'string',
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

    // Boot method for model lifecycle hooks
    protected static function boot()
    {
        parent::boot();

        // Delete associated files (including cover images) when publication is deleted
        static::deleting(function ($publication) {
            // Soft delete: cascade delete cover images through File model
            // This will trigger File::deleting hook to clean up physical files
            $publication->files()->where('file_type', 'cover')->delete();
        });

        // Force delete: clean up all files including those soft-deleted
        static::forceDeleting(function ($publication) {
            // Force delete all files associated with this publication
            $publication->files()->withTrashed()->where('file_type', 'cover')->forceDelete();
        });
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

    // New Relationships (Task 3: Cover Images)
    public function coverImage(): HasMany
    {
        return $this->hasMany(File::class, 'id_publication', 'id_publication')
            ->where('file_type', 'cover')
            ->latest('created_at');
    }

    // New Relationships (Task 2: Genres)
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'genre_publication',
            'publication_id',
            'genre_id'
        )->withTimestamps();
    }

    // Accessors for cover image and genres (Task 7)
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cover = $this->coverImage()->first();
                if (!$cover || !$cover->file_name) {
                    return null;
                }

                // Generate public URL for cover image (no auth required)
                $encodedFilename = rtrim(strtr(base64_encode($cover->file_name), '+/', '-_'), '=');
                return route('covers.serve', [
                    'publication' => $this->id_publication,
                    'filename' => $encodedFilename,
                ]);
            }
        );
    }

    /**
     * Get the relative file path for cover image (used in templates with Storage::url())
     */
    protected function coverImagePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Check if files relationship is already loaded, otherwise load it
                if (!$this->relationLoaded('files')) {
                    $cover = $this->coverImage()->first();
                } else {
                    $cover = $this->files->where('file_type', 'cover')->first();
                }

                if (!$cover || !$cover->file_name) {
                    return null;
                }

                // Generate public URL for cover image (no auth required)
                $encodedFilename = rtrim(strtr(base64_encode($cover->file_name), '+/', '-_'), '=');
                return route('covers.serve', [
                    'publication' => $this->id_publication,
                    'filename' => $encodedFilename,
                ]);
            }
        );
    }

    protected function primaryGenres(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->genres()
                    ->limit(3)
                    ->get()
                    ->map(fn ($genre) => $genre->name_en)
                    ->toArray();
            }
        );
    }
}
