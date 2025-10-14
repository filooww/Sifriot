<?php

namespace Database\Factories;

use App\Models\Publishing;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublishingFactory extends Factory
{
    protected $model = Publishing::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
