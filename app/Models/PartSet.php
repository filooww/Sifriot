<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartSet extends Model
{
    protected $table = 'part_sets';

    protected $primaryKey = 'id_part_set';

    protected $fillable = [
        'part_set',
    ];

    // Relationships
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'id_part_set', 'id_part_set');
    }
}
