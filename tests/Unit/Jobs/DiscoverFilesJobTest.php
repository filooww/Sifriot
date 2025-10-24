<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DiscoverFilesJob;
use App\Models\FolderScanJob;
use App\Models\User;
use App\Services\FolderScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DiscoverFilesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_discover_files_job_calls_scan_service(): void
    {
        Storage::fake('library');
        Storage::disk('library')->put('content/books/test.pdf', 'content');

        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create([
            'user_id' => $user->id,
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => false,
                'file_format_filters' => ['pdf'],
            ],
        ]);

        $job = new DiscoverFilesJob($scanJob->id);
        $job->handle($this->app->make(FolderScanService::class));

        $scanJob->refresh();
        $this->assertEquals('processing', $scanJob->status);
        $this->assertGreaterThan(0, $scanJob->total_files_found);
    }

    public function test_discover_files_job_handles_failure(): void
    {
        $scanJob = FolderScanJob::factory()->create();

        $job = new DiscoverFilesJob($scanJob->id);
        $exception = new \Exception('Test error');

        $job->failed($exception);

        $scanJob->refresh();
        $this->assertEquals('failed', $scanJob->status);
    }
}
