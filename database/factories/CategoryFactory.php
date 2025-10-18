<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'parent_id' => null,
            'name_en' => ucfirst($name),
            'name_ru' => ucfirst($name),
            'name_he' => ucfirst($name),
            'slug' => str($name)->slug(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
