<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeSet extends Model
{
    use HasFactory;

    protected $table = 'theme_sets';
    protected $primaryKey = 'id_theme_set';

    protected $fillable = [
        'theme_set',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_theme_set', 'id_theme_set');
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class, 'id_theme_set', 'id_theme_set');
    }
}
