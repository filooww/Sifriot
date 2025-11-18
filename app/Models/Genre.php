<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    protected $table = 'genres';

    protected $fillable = [
        'name_en',
        'name_ru',
        'name_he',
        'slug',
    ];

    // Relationships
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'genre_publication',
            'genre_id',
            'publication_id'
        )->withTimestamps();
    }
}
