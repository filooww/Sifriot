<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Events\FolderScanCompleted;
use App\Jobs\DiscoverFilesJob;
use App\Jobs\ProcessFileRegistrationJob;
use App\Livewire\Admin\BulkFolderScanner;
use App\Livewire\Admin\ScanResultsViewer;
use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use App\Models\Publication;
use App\Models\User;
use App\Services\FolderScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BulkFolderScanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test: Admin can initiate bulk scan with options (AC: 1, 2).
     */
    public function test_admin_can_initiate_bulk_scan_with_options(): void
    {
        Queue::fake();
        Storage::fake('library');

        // Create test folder structure
        Storage::disk('library')->put('content/books/test.pdf', 'content');

        Livewire::actingAs($this->admin)
            ->test(BulkFolderScanner::class)
            ->set('folderPath', 'content/books')
            ->set('recursive', true)
            ->set('fileFormatFilters', ['pdf', 'epub'])
            ->set('maxDepth', 3)
            ->call('startScan')
            ->assertHasNoErrors()
            ->assertDispatched('scan-started');

        Queue::assertPushed(DiscoverFilesJob::class);

        $this->assertDatabaseHas('folder_scan_jobs', [
            'folder_path' => 'content/books',
            'status' => 'pending',
            'user_id' => $this->admin->id,
        ]);
    }

    /**
     * Test: Scan runs as background queue job (AC: 3, IV: 1).
     */
    public function test_scan_runs_as_background_queue_job(): void
    {
        Queue::fake();
        Storage::fake('library');

        Storage::disk('library')->put('content/books/test.pdf', 'content');

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'status' => 'pending',
            'user_id' => $this->admin->id,
        ]);

        Queue::assertNothingPushed();

        $service = app(FolderScanService::class);
        $service->initiateScan('content/books', ['recursive' => true], $this->admin->id);

        Queue::assertPushed(DiscoverFilesJob::class);
    }

    /**
     * Test: System discovers files recursively (AC: 4).
     */
    public function test_system_discovers_files_recursively(): void
    {
        Storage::fake('library');

        // Create nested folder structure
        Storage::disk('library')->put('content/books/book1.pdf', 'content');
        Storage::disk('library')->put('content/books/subfolder/book2.pdf', 'content');
        Storage::disk('library')->put('content/books/subfolder/deep/book3.epub', 'content');

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => ['recursive' => true, 'file_format_filters' => ['pdf', 'epub']],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $scanJob->refresh();

        // Should find all 3 files
        $this->assertEquals(3, $scanJob->total_files_found);
        $this->assertEquals('processing', $scanJob->status);
    }

    /**
     * Test: Already-registered files automatically skipped (AC: 8).
     */
    public function test_already_registered_files_are_skipped(): void
    {
        Storage::fake('library');

        Storage::disk('library')->put('content/books/book1.pdf', 'content');
        Storage::disk('library')->put('content/books/book2.pdf', 'content');

        // Register book1.pdf beforehand
        FileRegistrationLog::factory()->create([
            'file_path' => 'content/books/book1.pdf',
            'status' => 'processed',
            'registration_source' => 'manual',
        ]);

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => ['recursive' => false, 'file_format_filters' => ['pdf']],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $scanJob->refresh();

        // Should skip book1.pdf (already registered)
        $this->assertEquals(1, $scanJob->files_skipped);
        $this->assertEquals(2, $scanJob->total_files_found);
    }

    /**
     * Test: Successful registrations create pending publications (AC: 10).
     */
    public function test_successful_registrations_create_pending_publications(): void
    {
        Storage::fake('library');

        Storage::disk('library')->put('content/books/book1.pdf', 'content');

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'status' => 'processing',
        ]);

        $job = new ProcessFileRegistrationJob('content/books/book1.pdf', $scanJob->id);
        $job->handle(app(\App\Services\FileStorageService::class));

        $this->assertDatabaseHas('publications', [
            'original_folder_path' => 'content/books',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('file_registration_logs', [
            'file_path' => 'content/books/book1.pdf',
            'folder_scan_job_id' => $scanJob->id,
            'registration_source' => 'bulk_scan',
            'status' => 'processed',
        ]);
    }

    /**
     * Test: Failed registrations logged with error reasons (AC: 9, IV: 2).
     */
    public function test_failed_registrations_logged_with_error_reasons(): void
    {
        Storage::fake('library');

        // Create scan job but don't create the file (will fail)
        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'status' => 'processing',
        ]);

        $job = new ProcessFileRegistrationJob('content/books/nonexistent.pdf', $scanJob->id);

        try {
            $job->handle(app(\App\Services\FileStorageService::class));
        } catch (\Exception $e) {
            $job->failed($e);
        }

        $scanJob->refresh();

        $this->assertGreaterThan(0, $scanJob->files_failed);

        $this->assertDatabaseHas('file_registration_logs', [
            'file_path' => 'content/books/nonexistent.pdf',
            'folder_scan_job_id' => $scanJob->id,
            'status' => 'failed',
        ]);
    }

    /**
     * Test: Scan progress updates in real-time (AC: 7, IV: 4).
     */
    public function test_scan_progress_updates_in_realtime(): void
    {
        $scanJob = FolderScanJob::factory()->create([
            'total_files_found' => 100,
            'files_registered' => 50,
            'files_skipped' => 20,
            'files_failed' => 5,
            'status' => 'processing',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BulkFolderScanner::class)
            ->set('currentScanJob', $scanJob)
            ->call('refreshProgress')
            ->assertSet('currentScanJob.files_registered', 50)
            ->assertSet('currentScanJob.files_skipped', 20)
            ->assertSet('currentScanJob.files_failed', 5)
            ->assertDispatched('scan-progress-updated');
    }

    /**
     * Test: Admin can pause scan (AC: 12).
     */
    public function test_admin_can_pause_scan(): void
    {
        $scanJob = FolderScanJob::factory()->create([
            'status' => 'processing',
            'user_id' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(BulkFolderScanner::class)
            ->set('currentScanJob', $scanJob)
            ->call('pauseScan');

        $scanJob->refresh();

        $this->assertEquals('paused', $scanJob->status);
    }

    /**
     * Test: Admin can cancel scan (AC: 12, IV: 5).
     */
    public function test_admin_can_cancel_scan(): void
    {
        Storage::fake('library');

        $scanJob = FolderScanJob::factory()->create([
            'status' => 'processing',
            'user_id' => $this->admin->id,
            'files_registered' => 10,
        ]);

        // Create some already-processed registrations
        FileRegistrationLog::factory()->count(10)->create([
            'folder_scan_job_id' => $scanJob->id,
            'status' => 'processed',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BulkFolderScanner::class)
            ->set('currentScanJob', $scanJob)
            ->call('cancelScan');

        $scanJob->refresh();

        $this->assertEquals('cancelled', $scanJob->status);

        // Verify completed registrations still exist
        $this->assertEquals(10, FileRegistrationLog::where('folder_scan_job_id', $scanJob->id)->count());
    }

    /**
     * Test: Scan results filterable (AC: 13).
     */
    public function test_scan_results_are_filterable(): void
    {
        $scanJob = FolderScanJob::factory()->create();

        FileRegistrationLog::factory()->count(5)->create([
            'folder_scan_job_id' => $scanJob->id,
            'status' => 'processed',
        ]);

        FileRegistrationLog::factory()->count(3)->create([
            'folder_scan_job_id' => $scanJob->id,
            'status' => 'failed',
        ]);

        // Test "All" filter
        Livewire::actingAs($this->admin)
            ->test(ScanResultsViewer::class, ['scanJobId' => $scanJob->id])
            ->assertViewHas('results', function ($results) {
                return $results->total() === 8;
            });

        // Test "Failed" filter
        Livewire::actingAs($this->admin)
            ->test(ScanResultsViewer::class, ['scanJobId' => $scanJob->id])
            ->set('filterStatus', 'failed')
            ->assertViewHas('results', function ($results) {
                return $results->total() === 3;
            });

        // Test "Success" filter
        Livewire::actingAs($this->admin)
            ->test(ScanResultsViewer::class, ['scanJobId' => $scanJob->id])
            ->set('filterStatus', 'processed')
            ->assertViewHas('results', function ($results) {
                return $results->total() === 5;
            });
    }

    /**
     * Test: Bulk approve changes publication status (AC: 14).
     */
    public function test_bulk_approve_changes_publication_status(): void
    {
        $scanJob = FolderScanJob::factory()->create();

        $publications = Publication::factory()->count(5)->create([
            'status' => 'pending',
            'original_folder_path' => 'content/books',
        ]);

        foreach ($publications as $publication) {
            FileRegistrationLog::factory()->create([
                'folder_scan_job_id' => $scanJob->id,
                'publication_id' => $publication->id,
                'status' => 'processed',
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(ScanResultsViewer::class, ['scanJobId' => $scanJob->id])
            ->call('bulkApprove');

        foreach ($publications as $publication) {
            $publication->refresh();
            $this->assertEquals('published', $publication->status);
        }
    }

    /**
     * Test: Summary report generated after completion (AC: 11).
     */
    public function test_summary_report_generated_after_completion(): void
    {
        Event::fake();

        $scanJob = FolderScanJob::factory()->create([
            'status' => 'processing',
            'started_at' => now()->subMinutes(10),
            'total_files_found' => 100,
            'files_registered' => 80,
            'files_skipped' => 15,
            'files_failed' => 5,
        ]);

        $service = app(FolderScanService::class);
        $service->completeScan($scanJob);

        $scanJob->refresh();

        $this->assertEquals('completed', $scanJob->status);
        $this->assertNotNull($scanJob->completed_at);
        $this->assertNotNull($scanJob->processing_time_seconds);
        $this->assertGreaterThan(0, $scanJob->processing_time_seconds);

        Event::assertDispatched(FolderScanCompleted::class);
    }

    /**
     * Test: Large bulk scan (1000+ files) completes without crash (IV: 3).
     */
    public function test_large_bulk_scan_completes_without_crash(): void
    {
        Storage::fake('library');

        // Create 1000 mock files
        for ($i = 0; $i < 1000; $i++) {
            Storage::disk('library')->put("content/books/file{$i}.pdf", 'content');
        }

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => ['recursive' => false, 'file_format_filters' => ['pdf']],
            'status' => 'pending',
        ]);

        $memoryBefore = memory_get_usage();

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $memoryAfter = memory_get_peak_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $scanJob->refresh();

        $this->assertEquals(1000, $scanJob->total_files_found);

        // Assert memory usage < 128MB for discovery phase
        $this->assertLessThan(128 * 1024 * 1024, $memoryUsed, 'File discovery used more than 128MB of memory');
    }
}
