<?php

namespace Database\Factories;

use App\Models\Magazine;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagazineFactory extends Factory
{
    protected $model = Magazine::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
