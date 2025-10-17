<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorGroup extends Model
{
    use HasFactory;

    protected $table = 'author_groups';

    protected $primaryKey = 'id_author_group';

    protected $fillable = [
        'author_group',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_author_set', 'id_author_group');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class, 'id_author_group', 'id_author_group');
    }
}
