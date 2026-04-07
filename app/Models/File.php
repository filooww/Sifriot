<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AutoLowercasesField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use AutoLowercasesField, HasFactory, SoftDeletes;

    protected array $autoLowercase = ['file_name' => 'file_name_low'];

    protected $table = 'files';

    // Auto-increment id is now the primary key (added via migration)
    // Eloquent auto-detects 'id' as PK

    protected $fillable = [
        'id_publication',
        'file_name',
        'file_name_low',
        'file_description',
        'file_issue_year',
        'ord_num',
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

    // Delete physical file from storage when File record is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            if ($file->file_path && $file->file_type === 'cover') {
                try {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
                    \Illuminate\Support\Facades\Log::warning('Failed to delete cover image from storage', [
                        'file_path' => $file->file_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
