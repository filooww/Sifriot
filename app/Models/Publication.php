<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'publications';

    protected $primaryKey = 'id_publication';

    protected $fillable = [
        'title',
        'title_low',
        'id_part',
        'issue_year',
        'id_issue_type',
        'id_magazine',
        'upload_date',
        'status',
        'original_folder_path',
        'content_type_id',
        'description',
        'word_count',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'word_count' => 'integer',
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_upload_date',
        'total_file_size',
    ];

    // Relationships
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

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(
            Section::class,
            'section_publication',
            'publication_id',
            'section_id',
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
        return $this->hasMany(FileMetadata::class, 'publication_id', 'id_publication');
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

    // Publishers Relationship
    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(
            Publisher::class,
            'publisher_publication',
            'publication_id',
            'publisher_id',
            'id_publication',
            'id'
        )->withTimestamps();
    }

    /**
     * Get cover image URL for this publication.
     * Uses pre-loaded files relationship if available to avoid N+1 queries.
     */
    protected function coverImagePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('files')) {
                    $cover = $this->files->where('file_type', 'cover')->first();
                } else {
                    $cover = $this->coverImage()->first();
                }

                if (! $cover || ! $cover->file_name) {
                    return null;
                }

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

    // Custom Field Relationships and Methods
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'fieldable');
    }

    /**
     * Get the value of a specific custom field by field name.
     */
    public function getCustomFieldValue(string $fieldName): mixed
    {
        $value = $this->customFieldValues()
            ->whereHas('customField', function ($query) use ($fieldName) {
                $query->where('field_name', $fieldName);
            })
            ->with('customField')
            ->first();

        return $value ? $value->getTypedValue() : null;
    }

    /**
     * Set the value of a specific custom field by field name.
     */
    public function setCustomFieldValue(string $fieldName, mixed $value): void
    {
        $customField = CustomField::where('field_name', $fieldName)
            ->where('content_type_id', $this->content_type_id)
            ->first();

        if (! $customField) {
            return;
        }

        $this->customFieldValues()->updateOrCreate(
            [
                'custom_field_id' => $customField->id,
                'fieldable_type' => self::class,
                'fieldable_id' => $this->id_publication,
            ],
            [
                'value' => is_array($value) ? $value : [$value],
            ]
        );
    }

    /**
     * Get all custom fields with their values for this publication.
     */
    public function getAllCustomFieldsWithValues(): array
    {
        if (! $this->content_type_id) {
            return [];
        }

        $customFields = CustomField::where('content_type_id', $this->content_type_id)
            ->orderedBySortOrder()
            ->get();

        $values = $this->customFieldValues()->with('customField')->get()->keyBy('custom_field_id');

        return $customFields->map(function ($field) use ($values) {
            return [
                'field' => $field,
                'value' => $values->has($field->id) ? $values->get($field->id)->getTypedValue() : null,
            ];
        })->toArray();
    }
}
