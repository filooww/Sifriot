<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldValueFactory extends Factory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        $customField = CustomField::inRandomOrder()->first() ?? CustomField::factory()->create();

        return [
            'custom_field_id' => $customField->id,
            'fieldable_type' => Publication::class,
            'fieldable_id' => Publication::factory(),
            'value' => $this->generateValueForFieldType($customField->field_type, $customField->field_config),
        ];
    }

    private function generateValueForFieldType(string $fieldType, ?array $config): array
    {
        return match ($fieldType) {
            'text' => [fake()->sentence()],
            'number' => [fake()->numberBetween($config['min'] ?? 0, $config['max'] ?? 1000)],
            'date' => [fake()->date()],
            'dropdown' => isset($config['options']) ? [fake()->randomElement(array_column($config['options'], 'value'))] : ['option1'],
            'multiselect' => isset($config['options']) ? fake()->randomElements(array_column($config['options'], 'value'), 2) : ['option1', 'option2'],
            'boolean' => [fake()->boolean()],
            'long_text' => [fake()->paragraph()],
            default => [fake()->word()],
        };
    }
}
