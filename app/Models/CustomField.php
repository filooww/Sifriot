<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content_type_id',
        'field_name',
        'label_en',
        'label_ru',
        'label_he',
        'field_type',
        'field_config',
        'is_required',
        'visibility',
        'is_searchable',
        'is_filterable',
        'sort_order',
    ];

    protected $casts = [
        'field_config' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the content type that owns the custom field.
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get all custom field values for this field.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /**
     * Get the localized label based on the current app locale.
     */
    public function getLocalizedLabelAttribute(): string
    {
        $locale = app()->getLocale();
        $labelKey = "label_{$locale}";

        return $this->$labelKey ?? $this->label_en;
    }

    /**
     * Scope a query to only include public custom fields.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include searchable custom fields.
     */
    public function scopeSearchable(Builder $query): Builder
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope a query to only include filterable custom fields.
     */
    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrderedBySortOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
