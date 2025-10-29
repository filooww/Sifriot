<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\File;
use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use App\Models\Publication;
use App\Services\FileStorageService;
use App\Services\FolderScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessFileRegistrationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public int $scanJobId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FileStorageService $fileStorage, FolderScanService $folderScanService): void
    {
        $scanJob = FolderScanJob::findOrFail($this->scanJobId);

        // Check if scan was cancelled or paused
        if (in_array($scanJob->status, ['cancelled', 'paused'])) {
            Log::channel('folder_scan')->info('Skipping file registration - scan not active', [
                'file_path' => $this->filePath,
                'scan_job_id' => $this->scanJobId,
                'status' => $scanJob->status,
            ]);

            return;
        }

        // Validate file still exists on library disk
        if (! Storage::disk('library')->exists($this->filePath)) {
            $this->recordFailure($scanJob, 'File not found');

            return;
        }

        try {
            DB::transaction(function () use ($fileStorage, $scanJob) {
                // Get file metadata
                $fullPath = Storage::disk('library')->path($this->filePath);
                $metadata = $fileStorage->getFileMetadata($this->filePath);

                // Create publication record with pending status
                $title = $metadata['suggested_title'] ?? basename($this->filePath);
                $publication = Publication::create([
                    'title' => $title,
                    'title_low' => strtolower($title),
                    'status' => 'pending',
                    'upload_date' => now(),
                    'original_folder_path' => dirname($this->filePath),
                    'content_type_id' => $metadata['content_type_id'] ?? $this->determineContentTypeId($this->filePath),
                ]);

                // Create file record
                // Get the next ord_num for this publication (default to 1 for first file)
                $nextOrdNum = File::where('id_publication', $publication->id_publication)->max('ord_num') ?? 0;
                $nextOrdNum++;

                File::create([
                    'id_publication' => $publication->id_publication,
                    'ord_num' => $nextOrdNum,
                    'file_name' => basename($this->filePath),
                    'file_name_low' => strtolower(basename($this->filePath)),
                    'file_source' => dirname($this->filePath), // Store relative directory path for library disk
                    'mime_type' => $metadata['mime_type'] ?? 'application/octet-stream',
                    'file_size_bytes' => $metadata['file_size'] ?? 0,
                ]);

                // Create registration log
                FileRegistrationLog::create([
                    'publication_id' => $publication->id_publication,
                    'file_path' => $fullPath,
                    'registration_source' => 'bulk_scan',
                    'folder_scan_job_id' => $this->scanJobId,
                    'metadata_auto_extracted' => true,
                    'status' => 'processed',
                    'registered_by' => $scanJob->user_id,
                ]);

                // Increment registered count
                $scanJob->increment('files_registered');

                Log::channel('folder_scan')->info('File registered successfully', [
                    'file_path' => $this->filePath,
                    'scan_job_id' => $this->scanJobId,
                    'publication_id' => $publication->id_publication,
                ]);
            });

            // Check if all files have been processed and complete scan if done
            $folderScanService->checkAndCompleteScan($scanJob->fresh());
        } catch (Throwable $e) {
            $this->recordFailure($scanJob, $e->getMessage());
            // Check if all files have been processed after failure
            $folderScanService->checkAndCompleteScan($scanJob->fresh());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $scanJob = FolderScanJob::find($this->scanJobId);

        if ($scanJob) {
            $this->recordFailure($scanJob, $exception->getMessage());
            // Check if all files have been processed after job failure
            app(FolderScanService::class)->checkAndCompleteScan($scanJob->fresh());
        }
    }

    /**
     * Record a failed registration.
     */
    private function recordFailure(FolderScanJob $scanJob, string $errorMessage): void
    {
        // Increment failed count
        $scanJob->increment('files_failed');

        // Create failed registration log
        $fullPath = Storage::disk('library')->path($this->filePath);
        FileRegistrationLog::create([
            'publication_id' => null,
            'file_path' => $fullPath,
            'registration_source' => 'bulk_scan',
            'folder_scan_job_id' => $this->scanJobId,
            'metadata_auto_extracted' => false,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'registered_by' => $scanJob->user_id,
        ]);

        Log::channel('folder_scan')->error('File registration failed', [
            'file_path' => $this->filePath,
            'scan_job_id' => $this->scanJobId,
            'error' => $errorMessage,
        ]);
    }

    /**
     * Determine content type ID based on file path.
     */
    private function determineContentTypeId(string $filePath): ?int
    {
        // Determine based on folder structure
        if (str_contains($filePath, '/books/')) {
            return 1; // Books
        } elseif (str_contains($filePath, '/magazines/')) {
            return 2; // Magazines
        } elseif (str_contains($filePath, '/articles/')) {
            return 3; // Articles
        }

        return 4; // Other
    }
}
