<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileMetadata>
 */
class FileMetadataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => $this->faker->uuid(),
            'file_name' => $this->faker->fileName('pdf'),
            'status' => 'pending',
            'extracted_data' => [],
            'extraction_method' => null,
            'confidence_scores' => [],
            'error_message' => null,
            'extracted_at' => null,
            'confirmed_at' => null,
            'rejected_at' => null,
        ];
    }

    /**
     * State: processed extraction (ready for review).
     */
    public function processed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processed',
                'extracted_data' => [
                    'title' => [
                        'value' => $this->faker->sentence(3),
                        'confidence' => 0.9,
                    ],
                    'authors' => [
                        [
                            'value' => $this->faker->name(),
                            'confidence' => 0.85,
                        ],
                    ],
                    'publication_year' => [
                        'value' => $this->faker->year(),
                        'confidence' => 0.8,
                    ],
                    'publisher' => [
                        'value' => $this->faker->company(),
                        'confidence' => 0.7,
                    ],
                ],
                'extraction_method' => 'PDFMetadataExtractor',
                'confidence_scores' => [
                    'title' => 0.9,
                    'authors' => 0.85,
                    'publication_year' => 0.8,
                    'publisher' => 0.7,
                ],
                'extracted_at' => now(),
            ];
        });
    }

    /**
     * State: confirmed extraction.
     */
    public function confirmed(): static
    {
        return $this->processed()->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ];
        });
    }

    /**
     * State: failed extraction.
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'extracted_data' => [],
                'error_message' => 'Unable to extract metadata from file',
                'extracted_at' => now(),
            ];
        });
    }

    /**
     * State: rejected extraction.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejected_at' => now(),
            ];
        });
    }
}
