<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractionRule extends Model
{
    use HasFactory;

    protected $table = 'extraction_rules';

    protected $fillable = [
        'content_type_id',
        'format',
        'priority',
        'pattern_type',
        'pattern',
        'target_field',
        'enabled',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'priority' => 'integer',
        'content_type_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to ContentType.
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class, 'content_type_id', 'id_content_type');
    }

    /**
     * Relationship: Belongs to User (creator).
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Belongs to User (updater).
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Get rules for a specific format.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $format  File format (pdf, epub, docx, etc.)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFormat($query, string $format)
    {
        return $query->where('format', strtolower($format));
    }

    /**
     * Scope: Get rules for a specific content type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByContentType($query, int $contentTypeId)
    {
        return $query->where('content_type_id', $contentTypeId);
    }

    /**
     * Scope: Get enabled rules only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope: Order by priority.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}
