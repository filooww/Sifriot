<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibraryPath>
 */
class LibraryPathFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => '/mnt/library/'.fake()->word(),
            'label' => fake()->optional()->words(3, true),
            'is_active' => true,
            'last_verified_at' => now(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
