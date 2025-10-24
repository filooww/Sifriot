<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FolderScanJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'folder_path',
        'scan_options',
        'status',
        'total_files_found',
        'files_registered',
        'files_skipped',
        'files_failed',
        'processing_time_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scan_options' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function fileRegistrationLogs(): HasMany
    {
        return $this->hasMany(FileRegistrationLog::class, 'folder_scan_job_id', 'id');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_files_found === 0) {
            return 0.0;
        }

        $processed = $this->files_registered + $this->files_skipped + $this->files_failed;

        return round(($processed / $this->total_files_found) * 100, 2);
    }
}
