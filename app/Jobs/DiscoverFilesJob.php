<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\FolderScanJob;
use App\Services\FolderScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscoverFilesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $scanJobId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FolderScanService $scanService): void
    {
        $scanJob = FolderScanJob::findOrFail($this->scanJobId);

        $scanService->discoverFiles($scanJob);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $scanJob = FolderScanJob::find($this->scanJobId);

        if ($scanJob) {
            $scanJob->update(['status' => 'failed']);

            Log::channel('folder_scan')->error('File discovery failed', [
                'scan_job_id' => $this->scanJobId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
