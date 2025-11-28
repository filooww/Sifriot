<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publishing extends Model
{
    use HasFactory;

    protected $table = 'publishings';

    protected $primaryKey = 'id_publishing';

    protected $fillable = [
        'publishing',
        'publishing_low',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_publishing', 'id_publishing');
    }
}
