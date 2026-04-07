<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasLocalizedName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, HasLocalizedName, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name_en',
        'name_ru',
        'name_he',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent section.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    /**
     * Get the child sections.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Section::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the publications for this section.
     */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'section_publication',
            'section_id',
            'publication_id',
            'id',
            'id_publication'
        );
    }


}
