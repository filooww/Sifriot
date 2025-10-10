<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
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
