<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory, SoftDeletes;
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

    // Automatically set lowercase version when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($author) {
            $author->author_low = mb_strtolower($author->author);
        });
    }
}
