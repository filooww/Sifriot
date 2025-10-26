<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\FolderScanJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderScanJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_folder_scan_job_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $scanJob->user);
        $this->assertEquals($user->id, $scanJob->user->id);
    }

    public function test_folder_scan_job_has_many_file_registration_logs(): void
    {
        $scanJob = FolderScanJob::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $scanJob->fileRegistrationLogs);
    }

    public function test_progress_percent_attribute_calculates_correctly(): void
    {
        $scanJob = FolderScanJob::factory()->create([
            'total_files_found' => 100,
            'files_registered' => 50,
            'files_skipped' => 20,
            'files_failed' => 10,
        ]);

        $this->assertEquals(80.0, $scanJob->progress_percent);
    }

    public function test_progress_percent_returns_zero_when_no_files_found(): void
    {
        $scanJob = FolderScanJob::factory()->create([
            'total_files_found' => 0,
        ]);

        $this->assertEquals(0.0, $scanJob->progress_percent);
    }

    public function test_scan_options_cast_to_array(): void
    {
        $options = [
            'recursive' => true,
            'file_format_filters' => ['pdf', 'epub'],
        ];

        $scanJob = FolderScanJob::factory()->create(['scan_options' => $options]);

        $this->assertIsArray($scanJob->scan_options);
        $this->assertEquals($options, $scanJob->scan_options);
    }
}
