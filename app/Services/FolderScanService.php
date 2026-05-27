<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\FolderScanCompleted;
use App\Jobs\DiscoverFilesJob;
use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FolderScanService
{
    public function __construct(
        private FileStorageService $fileStorageService
    ) {
    }

    public function initiateScan(string $folderPath, array $options, int $userId): FolderScanJob
    {
        // Validate folder path exists
        $this->fileStorageService->validateFilePath($folderPath);

        // Create scan job record
        $scanJob = FolderScanJob::create([
            'user_id' => $userId,
            'folder_path' => $folderPath,
            'scan_options' => $options,
            'status' => 'pending',
        ]);

        // Dispatch file discovery job
        DiscoverFilesJob::dispatchSync($scanJob->id);

        Log::channel('folder_scan')->info('Bulk scan initiated', [
            'scan_job_id' => $scanJob->id,
            'folder_path' => $folderPath,
            'user_id' => $userId,
        ]);

        return $scanJob;
    }

    public function discoverFiles(FolderScanJob $scanJob): void
    {
        // Update status to processing
        $scanJob->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $folderPath = $scanJob->folder_path;
        $options = $scanJob->scan_options;

        // Get files based on recursive option
        $files = $options['recursive'] ?? true
            ? Storage::disk('library')->allFiles($folderPath)
            : Storage::disk('library')->files($folderPath);

        // Apply file format filters
        $fileFormatFilters = $options['file_format_filters'] ?? ['pdf', 'epub', 'txt', 'doc', 'docx', 'fb2', 'djvu', 'rtf'];
        $filteredFiles = array_filter($files, function ($file) use ($fileFormatFilters) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            return in_array($extension, $fileFormatFilters);
        });

        $totalFiles = count($filteredFiles);
        $scanJob->update(['total_files_found' => $totalFiles]);

        $filesSkipped = 0;

        // Process each file
        foreach ($filteredFiles as $filePath) {
            // Check if already successfully registered
            $fullPath = Storage::disk('library')->path($filePath);
            if (FileRegistrationLog::where('file_path', $fullPath)->where('status', 'processed')->exists()) {
                $filesSkipped++;

                continue;
            }

            // Dispatch file registration job
            \App\Jobs\ProcessFileRegistrationJob::dispatchSync($filePath, $scanJob->id);
        }

        // Update skipped count
        $scanJob->update(['files_skipped' => $filesSkipped]);

        Log::channel('folder_scan')->info('File discovery completed', [
            'scan_job_id' => $scanJob->id,
            'total_files' => $totalFiles,
            'files_skipped' => $filesSkipped,
        ]);
    }

    public function pauseScan(FolderScanJob $scanJob): void
    {
        $scanJob->update(['status' => 'paused']);

        Log::channel('folder_scan')->info('Scan paused', [
            'scan_job_id' => $scanJob->id,
        ]);
    }

    public function cancelScan(FolderScanJob $scanJob): void
    {
        $scanJob->update(['status' => 'cancelled']);

        // Delete queued jobs from queue (only pending, not processing)
        DB::table('jobs')
            ->where('payload', 'like', '%ProcessFileRegistrationJob%')
            ->where('payload', 'like', '%"scanJobId":' . $scanJob->id . '%')
            ->delete();

        Log::channel('folder_scan')->info('Scan cancelled', [
            'scan_job_id' => $scanJob->id,
        ]);
    }

    public function checkAndCompleteScan(FolderScanJob $scanJob): void
    {
        // Check if all files have been processed
        $totalProcessed = $scanJob->files_registered + $scanJob->files_skipped + $scanJob->files_failed;

        if ($totalProcessed >= $scanJob->total_files_found && $scanJob->total_files_found > 0) {
            $this->completeScan($scanJob);
        }
    }

    public function completeScan(FolderScanJob $scanJob): void
    {
        // Only complete if not already completed
        if ($scanJob->status === 'completed') {
            return;
        }

        $scanJob->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Calculate processing time
        if ($scanJob->started_at && $scanJob->completed_at) {
            $processingTime = $scanJob->completed_at->diffInSeconds($scanJob->started_at);
            $scanJob->update(['processing_time_seconds' => $processingTime]);
        }

        // Dispatch completion event
        event(new FolderScanCompleted($scanJob));

        Log::channel('folder_scan')->info('Scan completed', [
            'scan_job_id' => $scanJob->id,
            'files_registered' => $scanJob->files_registered,
            'files_skipped' => $scanJob->files_skipped,
            'files_failed' => $scanJob->files_failed,
            'processing_time_seconds' => $scanJob->processing_time_seconds,
        ]);
    }
}
