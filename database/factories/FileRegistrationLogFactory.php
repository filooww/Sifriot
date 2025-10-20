<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileRegistrationLog>
 */
class FileRegistrationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => null,
            'file_path' => 'content/books/'.fake()->uuid().'.pdf',
            'registration_source' => fake()->randomElement(['manual_registration', 'admin_upload', 'bulk_scan']),
            'folder_scan_job_id' => null,
            'metadata_auto_extracted' => false,
            'status' => 'pending',
            'error_message' => null,
            'registered_by' => 1, // Will be overridden in tests
        ];
    }
}
