<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MetadataExtracted;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfMetadataReady
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(MetadataExtracted $event): void
    {
        $metadata = $event->fileMetadata;

        // Log notification event
        Log::channel('folder_scan')->info('Metadata extraction completed, ready for admin review', [
            'file_metadata_id' => $metadata->id,
            'file_name' => $metadata->file_name,
            'status' => $metadata->status,
            'has_title' => (bool) $metadata->getTitle(),
            'author_count' => count($metadata->getAuthors()),
        ]);

        // Future: Send notification to admin via email, database notification, or real-time event
        // Example: Notification::send($admins, new MetadataReadyNotification($metadata));
        // Example: Livewire event dispatch for real-time UI updates
    }
}
