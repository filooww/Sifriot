<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FolderScanJob;
use App\Services\FolderScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkScanPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Scan 10,000 files without memory issues (IV: 3).
     *
     * This test ensures that the bulk scan system can handle large-scale
     * operations without running out of memory or crashing.
     */
    public function test_scan_10000_files_without_memory_issues(): void
    {
        Storage::fake('library');

        // Create 10,000 mock files
        for ($i = 0; $i < 10000; $i++) {
            Storage::disk('library')->put("content/books/file{$i}.pdf", 'test content');
        }

        $memoryBefore = memory_get_usage();

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => false,
                'file_format_filters' => ['pdf', 'epub', 'txt', 'docx'],
            ],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $memoryAfter = memory_get_peak_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $scanJob->refresh();

        // Verify all files were discovered
        $this->assertEquals(10000, $scanJob->total_files_found);
        $this->assertEquals('processing', $scanJob->status);

        // Assert memory usage < 512MB
        $maxMemoryMB = 512;
        $maxMemoryBytes = $maxMemoryMB * 1024 * 1024;

        $this->assertLessThan(
            $maxMemoryBytes,
            $memoryUsed,
            sprintf(
                'Bulk scan used %.2f MB of memory, which exceeds the limit of %d MB',
                $memoryUsed / 1024 / 1024,
                $maxMemoryMB
            )
        );

        // Log memory usage for documentation
        $this->addToAssertionCount(1);
        echo sprintf(
            "\nMemory used for 10,000 file scan: %.2f MB (Limit: %d MB)\n",
            $memoryUsed / 1024 / 1024,
            $maxMemoryMB
        );
    }

    /**
     * Test: Scan with deep folder structure (max depth).
     */
    public function test_scan_with_deep_folder_structure(): void
    {
        Storage::fake('library');

        // Create nested folder structure with max depth of 10
        $depth = 10;
        $path = 'content/books';
        for ($i = 0; $i < $depth; $i++) {
            $path .= "/level{$i}";
            Storage::disk('library')->put("{$path}/file{$i}.pdf", 'content');
        }

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => true,
                'file_format_filters' => ['pdf'],
                'max_depth' => 5,
            ],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $scanJob->refresh();

        // Should find only files up to depth 5
        $this->assertLessThanOrEqual(5, $scanJob->total_files_found);
        $this->assertEquals('processing', $scanJob->status);
    }

    /**
     * Test: Concurrent scan operations don't interfere.
     */
    public function test_concurrent_scan_operations_dont_interfere(): void
    {
        Storage::fake('library');

        // Create two separate folder structures
        for ($i = 0; $i < 100; $i++) {
            Storage::disk('library')->put("content/books/file{$i}.pdf", 'content');
            Storage::disk('library')->put("content/magazines/file{$i}.pdf", 'content');
        }

        $scanJob1 = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => ['recursive' => false, 'file_format_filters' => ['pdf']],
            'status' => 'pending',
        ]);

        $scanJob2 = FolderScanJob::factory()->create([
            'folder_path' => 'content/magazines',
            'scan_options' => ['recursive' => false, 'file_format_filters' => ['pdf']],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);

        // Run both scans
        $service->discoverFiles($scanJob1);
        $service->discoverFiles($scanJob2);

        $scanJob1->refresh();
        $scanJob2->refresh();

        // Both scans should complete independently
        $this->assertEquals(100, $scanJob1->total_files_found);
        $this->assertEquals(100, $scanJob2->total_files_found);
        $this->assertEquals('processing', $scanJob1->status);
        $this->assertEquals('processing', $scanJob2->status);
    }

    /**
     * Test: Processing time is tracked accurately.
     */
    public function test_processing_time_is_tracked_accurately(): void
    {
        Storage::fake('library');

        // Create 100 test files
        for ($i = 0; $i < 100; $i++) {
            Storage::disk('library')->put("content/books/file{$i}.pdf", 'content');
        }

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => ['recursive' => false, 'file_format_filters' => ['pdf']],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        // Simulate processing delay
        sleep(2);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);
        $service->completeScan($scanJob);

        $scanJob->refresh();

        $this->assertEquals('completed', $scanJob->status);
        $this->assertNotNull($scanJob->processing_time_seconds);
        $this->assertGreaterThanOrEqual(2, $scanJob->processing_time_seconds);
    }

    /**
     * Test: File format filters work correctly with large datasets.
     */
    public function test_file_format_filters_work_with_large_datasets(): void
    {
        Storage::fake('library');

        // Create 1000 files of different formats
        for ($i = 0; $i < 250; $i++) {
            Storage::disk('library')->put("content/books/file{$i}.pdf", 'content');
            Storage::disk('library')->put("content/books/file{$i}.epub", 'content');
            Storage::disk('library')->put("content/books/file{$i}.txt", 'content');
            Storage::disk('library')->put("content/books/file{$i}.docx", 'content');
        }

        $scanJob = FolderScanJob::factory()->create([
            'folder_path' => 'content/books',
            'scan_options' => [
                'recursive' => false,
                'file_format_filters' => ['pdf', 'epub'], // Only PDF and EPUB
            ],
            'status' => 'pending',
        ]);

        $service = app(FolderScanService::class);
        $service->discoverFiles($scanJob);

        $scanJob->refresh();

        // Should find only PDF and EPUB files (500 total)
        $this->assertEquals(500, $scanJob->total_files_found);
    }
}
