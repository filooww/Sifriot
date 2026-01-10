<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContentType;
use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        return [
            'content_type_id' => ContentType::factory(),
            'field_name' => fake()->unique()->slug(2),
            'label_en' => fake()->words(2, true),
            'label_ru' => fake()->words(2, true),
            'label_he' => fake()->words(2, true),
            'field_type' => fake()->randomElement(['text', 'number', 'date', 'dropdown', 'multiselect', 'boolean', 'long_text']),
            'field_config' => [],
            'is_required' => fake()->boolean(30),
            'visibility' => fake()->randomElement(['public', 'admin_only', 'hidden']),
            'is_searchable' => fake()->boolean(50),
            'is_filterable' => fake()->boolean(40),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'text',
            'field_config' => ['max_length' => 255],
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'number',
            'field_config' => ['min' => 0, 'max' => 10000, 'step' => 1],
        ]);
    }

    public function dropdown(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'dropdown',
            'field_config' => [
                'options' => [
                    ['value' => 'option1', 'label_en' => 'Option 1', 'label_ru' => 'Опция 1', 'label_he' => 'אפשרות 1'],
                    ['value' => 'option2', 'label_en' => 'Option 2', 'label_ru' => 'Опция 2', 'label_he' => 'אפשרות 2'],
                    ['value' => 'option3', 'label_en' => 'Option 3', 'label_ru' => 'Опция 3', 'label_he' => 'אפשרות 3'],
                ],
            ],
        ]);
    }

    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'boolean',
            'field_config' => [],
        ]);
    }

    public function date(): static
    {
        return $this->state(fn (array $attributes) => [
            'field_type' => 'date',
            'field_config' => ['min_date' => '1900-01-01', 'max_date' => '2100-12-31'],
        ]);
    }
}
