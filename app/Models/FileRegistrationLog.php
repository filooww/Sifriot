<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRegistrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'file_path',
        'registration_source',
        'folder_scan_job_id',
        'metadata_auto_extracted',
        'status',
        'error_message',
        'registered_by',
    ];

    protected $casts = [
        'metadata_auto_extracted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id', 'id_publication');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
