<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_id',
        'fieldable_type',
        'fieldable_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get the parent fieldable model (Publication, etc.).
     */
    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the custom field that owns this value.
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }

    /**
     * Get the value cast to the appropriate PHP type based on field_type.
     */
    public function getTypedValue(): mixed
    {
        if (!$this->customField) {
            return $this->value;
        }

        $fieldType = $this->customField->field_type;
        $rawValue = is_array($this->value) && isset($this->value[0]) ? $this->value[0] : $this->value;

        return match ($fieldType) {
            'number' => is_numeric($rawValue) ? (float) $rawValue : null,
            'boolean' => (bool) $rawValue,
            'date' => $rawValue,
            'dropdown' => $rawValue,
            'multiselect' => is_array($this->value) ? $this->value : [$rawValue],
            'text', 'long_text' => (string) $rawValue,
            default => $rawValue,
        };
    }
}
