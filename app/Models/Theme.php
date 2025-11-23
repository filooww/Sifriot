<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Theme extends Model
{
    use HasFactory;

    protected $table = 'themes';

    protected $primaryKey = 'id_theme';

    protected $fillable = [
        'theme',
        'theme_low',
    ];

    // Relationships
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'publication_theme',
            'id_theme',
            'id_publication',
            'id_theme',
            'id_publication'
        )->withTimestamps();
    }
}
