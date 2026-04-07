<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Provides localized name access for models with name_en, name_ru, name_he columns.
 *
 * Usage:
 *   1. Add `use HasLocalizedName;` to your model.
 *   2. Optionally set `LOCALIZED_NAME_PREFIX` constant if column prefix differs from 'name'.
 *      Defaults to 'name', which maps to name_en, name_ru, name_he.
 *
 * Provides:
 *   - $model->localized_name  (Attribute accessor)
 *
 * Pattern: Template Method -- the trait defines the algorithm (get locale, build column name, fallback),
 * and the model customizes it via the constant.
 */
trait HasLocalizedName
{
    /**
     * Column prefix for localized fields. Override in model if needed.
     * e.g., 'name' -> name_en, name_ru, name_he
     *       'label' -> label_en, label_ru, label_he
     */
    protected function getLocalizedNamePrefix(): string
    {
        return defined('static::LOCALIZED_NAME_PREFIX') ? static::LOCALIZED_NAME_PREFIX : 'name';
    }

    /**
     * Get the localized name based on current app locale.
     * Falls back to English if the locale column is empty.
     */
    protected function getLocalizedNameAttribute(): string
    {
        $prefix = $this->getLocalizedNamePrefix();
        $locale = app()->getLocale();
        $localizedColumn = "{$prefix}_{$locale}";
        $fallbackColumn = "{$prefix}_en";

        return $this->$localizedColumn ?? $this->$fallbackColumn ?? '';
    }
}
