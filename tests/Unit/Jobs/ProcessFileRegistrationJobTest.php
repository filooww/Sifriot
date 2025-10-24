<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessFileRegistrationJob;
use App\Models\FolderScanJob;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessFileRegistrationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_file_registration(): void
    {
        Storage::fake('library');
        Storage::disk('library')->put('content/books/test.pdf', 'test content');

        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create(['user_id' => $user->id]);

        $job = new ProcessFileRegistrationJob('content/books/test.pdf', $scanJob->id);
        $job->handle($this->app->make(FileStorageService::class));

        $scanJob->refresh();
        $this->assertEquals(1, $scanJob->files_registered);

        $this->assertDatabaseHas('publications', [
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('file_registration_logs', [
            'folder_scan_job_id' => $scanJob->id,
            'registration_source' => 'bulk_scan',
            'status' => 'processed',
        ]);
    }

    public function test_file_registration_fails_when_file_missing(): void
    {
        Storage::fake('library');

        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create(['user_id' => $user->id]);

        $job = new ProcessFileRegistrationJob('content/books/missing.pdf', $scanJob->id);
        $job->handle($this->app->make(FileStorageService::class));

        $scanJob->refresh();
        $this->assertEquals(1, $scanJob->files_failed);

        $this->assertDatabaseHas('file_registration_logs', [
            'folder_scan_job_id' => $scanJob->id,
            'status' => 'failed',
            'error_message' => 'File not found',
        ]);
    }

    public function test_file_registration_skipped_when_scan_cancelled(): void
    {
        Storage::fake('library');
        Storage::disk('library')->put('content/books/test.pdf', 'test content');

        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create([
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $job = new ProcessFileRegistrationJob('content/books/test.pdf', $scanJob->id);
        $job->handle($this->app->make(FileStorageService::class));

        $scanJob->refresh();
        $this->assertEquals(0, $scanJob->files_registered);
        $this->assertEquals(0, $scanJob->files_failed);
    }

    public function test_failed_method_records_failure(): void
    {
        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create(['user_id' => $user->id]);

        $job = new ProcessFileRegistrationJob('content/books/test.pdf', $scanJob->id);
        $exception = new \Exception('Test error');

        $job->failed($exception);

        $scanJob->refresh();
        $this->assertEquals(1, $scanJob->files_failed);
    }
}
