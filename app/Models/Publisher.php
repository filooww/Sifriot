<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_en',
        'name_ru',
        'name_he',
        'slug',
        'website',
    ];

    /**
     * Get the publications for this publisher.
     */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'publisher_publication',
            'publisher_id',
            'publication_id',
            'id',
            'id_publication'
        )->withTimestamps();
    }

    /**
     * Get the localized publisher name based on current locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        $column = 'name_'.$locale;

        return $this->$column ?? $this->name_en;
    }
}
