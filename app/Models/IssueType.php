<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueType extends Model
{
    use HasFactory;

    protected $table = 'issue_types';

    protected $primaryKey = 'id_issue_type';

    protected $fillable = [
        'issue_type',
    ];

    // Relationships
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_issue_type', 'id_issue_type');
    }
}
