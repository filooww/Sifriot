<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AutoLowercasesField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use AutoLowercasesField, HasFactory, SoftDeletes;

    protected array $autoLowercase = ['author' => 'author_low'];

    protected $table = 'authors';

    protected $primaryKey = 'id_author';

    protected $fillable = [
        'author',
        'author_low',
    ];

    // Relationships
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'author_publication',
            'id_author',
            'id_publication',
            'id_author',
            'id_publication'
        )->withPivot('order')->withTimestamps();
    }

}
