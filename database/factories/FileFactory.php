<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->word() . '.' . $this->faker->randomElement(['pdf', 'doc', 'docx', 'txt']);
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
        ];
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        return [
            'id_publication' => null, // Must be set when creating
            'file_name' => $fileName,
            'file_name_low' => mb_strtolower($fileName),
            'file_description' => $this->faker->optional()->sentence(),
            'file_issue_year' => $this->faker->optional()->year(),
            'file_volume' => $this->faker->optional()->numberBetween(1, 50),
            'file_number' => $this->faker->optional()->numberBetween(1, 100),
            'file_page' => $this->faker->optional()->numberBetween(1, 500),
            'ord_num' => $this->faker->numberBetween(1, 10),
            'file_size' => $this->faker->randomFloat(2, 0.1, 100),
            'file_source' => $this->faker->optional()->word(),
            'mime_type' => $mimeTypes[$extension] ?? 'application/octet-stream',
            'file_size_bytes' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
        ];
    }
}
