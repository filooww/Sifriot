<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'authors';
    protected $primaryKey = 'id_author';

    protected $fillable = [
        'author',
        'author_low',
    ];

    // Automatically set lowercase version when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($author) {
            $author->author_low = mb_strtolower($author->author);
        });
    }
}
