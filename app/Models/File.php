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
        'file_type',
        'file_path',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'file_type' => 'string',
    ];

    // Relationship
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    // Scope for cover images
    public function scopeCovers($query)
    {
        return $query->where('file_type', 'cover');
    }

    // Scope for content files
    public function scopeContent($query)
    {
        return $query->where('file_type', 'content');
    }

    // Auto-lowercase file_name and cleanup on delete
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($file) {
            $file->file_name_low = mb_strtolower($file->file_name);
        });

        // Delete physical file from storage when File record is deleted
        static::deleting(function ($file) {
            if ($file->file_path && $file->file_type === 'cover') {
                try {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to delete cover image from storage', [
                        'file_path' => $file->file_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
