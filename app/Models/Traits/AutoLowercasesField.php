<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Automatically generates lowercase versions of specified fields on save.
 *
 * Usage:
 *   1. Add `use AutoLowercasesField;` to your model.
 *   2. Define `protected array $autoLowercase = ['title' => 'title_low'];`
 *      where key = source field, value = lowercase target field.
 *
 * Pattern: Template Method + Observer -- the trait registers a boot hook (observer)
 * that applies the lowercase transformation (template method) to each mapped field.
 */
trait AutoLowercasesField
{
    protected static function bootAutoLowercasesField(): void
    {
        static::saving(function ($model) {
            foreach ($model->autoLowercase as $source => $target) {
                if (isset($model->$source)) {
                    $model->$target = mb_strtolower($model->$source);
                }
            }
        });
    }
}
