<?php

namespace Database\Factories;

use App\Models\ThemeSet;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeSetFactory extends Factory
{
    protected $model = ThemeSet::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
