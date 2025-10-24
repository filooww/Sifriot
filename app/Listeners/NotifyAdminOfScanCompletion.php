<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolderScanCompleted;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfScanCompletion
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FolderScanCompleted $event): void
    {
        $scanJob = $event->scanJob;

        // Log completion
        Log::channel('folder_scan')->info('Bulk scan completed notification sent', [
            'scan_job_id' => $scanJob->id,
            'files_registered' => $scanJob->files_registered,
            'files_skipped' => $scanJob->files_skipped,
            'files_failed' => $scanJob->files_failed,
        ]);

        // You could send a notification here using Laravel's notification system
        // For now, we're just logging the completion
    }
}
