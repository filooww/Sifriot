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
            'theme_set' => $this->faker->words(3, true),
        ];
    }
}
