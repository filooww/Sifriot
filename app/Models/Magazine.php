<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Magazine extends Model
{
    use HasFactory;
    protected $table = 'magazines';
    protected $primaryKey = 'id_magazine';

    protected $fillable = [
        'magazine',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_magazine', 'id_magazine');
    }
}
