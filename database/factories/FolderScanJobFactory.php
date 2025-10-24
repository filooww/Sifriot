<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FolderScanJob>
 */
class FolderScanJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => true,
                'file_format_filters' => ['pdf', 'epub', 'txt', 'docx'],
                'max_depth' => null,
            ],
            'status' => 'pending',
            'total_files_found' => 0,
            'files_registered' => 0,
            'files_skipped' => 0,
            'files_failed' => 0,
            'processing_time_seconds' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
