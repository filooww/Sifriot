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
        'actuality',
        'id_theme_set',
        'id_author_set',
        '_del_mark',
        'add_int',
        'add_char',
        'word_count',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'actuality' => 'integer',
        '_del_mark' => 'integer',
        'add_int' => 'integer',
        'word_count' => 'integer',
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

    // Scopes for backward compatibility with legacy _del_mark
    public function scopeNotDeleted($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('deleted_at')
              ->where('_del_mark', 0);
        });
    }

    public function scopeLegacyDeleted($query)
    {
        return $query->where('_del_mark', 1);
    }
}
