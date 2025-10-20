<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentType>
 */
class ContentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['book', 'magazine', 'article', 'document']);

        return [
            'name_en' => ucfirst($type).'s',
            'name_ru' => ucfirst($type).'s (RU)',
            'name_he' => ucfirst($type).'s (HE)',
            'slug' => $type.'s',
            'icon' => 'document',
            'folder_name' => $type.'s',
            'is_system' => false,
        ];
    }
}
