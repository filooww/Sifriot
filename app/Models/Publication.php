<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
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
    ];

    protected $casts = [
        'upload_date' => 'date',
        'actuality' => 'integer',
        '_del_mark' => 'integer',
        'add_int' => 'integer',
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

    // Scopes
    public function scopeNotDeleted($query)
    {
        return $query->where('_del_mark', 0);
    }

    public function scopeDeleted($query)
    {
        return $query->where('_del_mark', 1);
    }
}
