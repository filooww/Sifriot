<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $table = 'parts';

    protected $primaryKey = 'id_part';

    protected $fillable = [
        'part',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_part', 'id_part');
    }
}
