<?php

namespace Database\Factories;

use App\Models\IssueType;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueTypeFactory extends Factory
{
    protected $model = IssueType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
