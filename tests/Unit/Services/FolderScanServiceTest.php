<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Jobs\DiscoverFilesJob;
use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use App\Models\User;
use App\Services\FileStorageService;
use App\Services\FolderScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FolderScanServiceTest extends TestCase
{
    use RefreshDatabase;

    private FolderScanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $fileStorageService = $this->app->make(FileStorageService::class);
        $this->service = new FolderScanService($fileStorageService);
    }

    public function test_initiate_scan_creates_folder_scan_job(): void
    {
        Queue::fake();
        Storage::fake('library');
        Storage::disk('library')->makeDirectory('content/books');

        $user = User::factory()->create();

        $scanJob = $this->service->initiateScan('content/books', [
            'recursive' => true,
            'file_format_filters' => ['pdf', 'epub'],
        ], $user->id);

        $this->assertInstanceOf(FolderScanJob::class, $scanJob);
        $this->assertEquals('pending', $scanJob->status);
        $this->assertEquals('content/books', $scanJob->folder_path);
        $this->assertEquals($user->id, $scanJob->user_id);

        Queue::assertPushed(DiscoverFilesJob::class);
    }

    public function test_discover_files_detects_files_and_skips_registered_ones(): void
    {
        Queue::fake();
        Storage::fake('library');

        // Create test files
        Storage::disk('library')->put('content/books/file1.pdf', 'content1');
        Storage::disk('library')->put('content/books/file2.epub', 'content2');
        Storage::disk('library')->put('content/books/file3.txt', 'content3');

        $user = User::factory()->create();

        // Mark one file as already registered
        $fullPath1 = Storage::disk('library')->path('content/books/file1.pdf');
        FileRegistrationLog::factory()->create([
            'file_path' => $fullPath1,
            'registered_by' => $user->id,
        ]);
        $scanJob = FolderScanJob::factory()->create([
            'user_id' => $user->id,
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => false,
                'file_format_filters' => ['pdf', 'epub', 'txt'],
            ],
        ]);

        $this->service->discoverFiles($scanJob);

        $scanJob->refresh();

        $this->assertEquals('processing', $scanJob->status);
        $this->assertEquals(3, $scanJob->total_files_found);
        $this->assertEquals(1, $scanJob->files_skipped);
    }

    public function test_pause_scan_updates_status(): void
    {
        $scanJob = FolderScanJob::factory()->create(['status' => 'processing']);

        $this->service->pauseScan($scanJob);

        $scanJob->refresh();
        $this->assertEquals('paused', $scanJob->status);
    }

    public function test_cancel_scan_stops_new_jobs(): void
    {
        $scanJob = FolderScanJob::factory()->create(['status' => 'processing']);

        $this->service->cancelScan($scanJob);

        $scanJob->refresh();
        $this->assertEquals('cancelled', $scanJob->status);
    }

    public function test_complete_scan_calculates_processing_time(): void
    {
        $startTime = now()->subSeconds(30);
        $scanJob = FolderScanJob::factory()->create([
            'status' => 'processing',
            'started_at' => $startTime,
        ]);

        $this->service->completeScan($scanJob);

        $scanJob->refresh();
        $this->assertEquals('completed', $scanJob->status);
        $this->assertNotNull($scanJob->completed_at);
        $this->assertNotNull($scanJob->processing_time_seconds);
        $this->assertGreaterThanOrEqual(30, $scanJob->processing_time_seconds);
        $this->assertLessThan(35, $scanJob->processing_time_seconds); // Allow some margin
    }
}
