<?php

namespace Database\Factories;

use App\Models\AuthorGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorGroupFactory extends Factory
{
    protected $model = AuthorGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
