<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'files';

    // Composite primary key
    protected $primaryKey = ['id_publication', 'file_name'];

    public $incrementing = false;

    protected $fillable = [
        'id_publication',
        'file_name',
        'file_name_low',
        'file_description',
        'file_issue_year',
        'file_volume',
        'file_number',
        'file_page',
        'ord_num',
        'file_size',
        'file_source',
        'mime_type',
        'file_size_bytes',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    // Relationship
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    // Auto-lowercase file_name
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($file) {
            $file->file_name_low = mb_strtolower($file->file_name);
        });
    }
}
