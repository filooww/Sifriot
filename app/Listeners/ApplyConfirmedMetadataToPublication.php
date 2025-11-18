<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MetadataConfirmed;
use App\Models\Publication;
use App\Models\Theme;
use Illuminate\Support\Facades\Log;

class ApplyConfirmedMetadataToPublication
{
    /**
     * Handle the event.
     *
     * NOTE: As of Story 1.21, metadata is saved to normalized tables directly
     * by MetadataReviewForm component during confirmExtraction() and saveManualEntry().
     * This listener now serves primarily as a logging/audit mechanism and shouldn't
     * duplicate the save logic to avoid conflicts and ensure single source of truth.
     */
    public function handle(MetadataConfirmed $event): void
    {
        $fileMetadata = $event->fileMetadata;

        Log::channel('folder_scan')->info('Metadata confirmed event fired - saved to normalized tables by MetadataReviewForm', [
            'file_metadata_id' => $fileMetadata->id,
            'file_name' => $fileMetadata->file_name,
            'status' => $fileMetadata->status,
            'confirmed_at' => $fileMetadata->confirmed_at,
            'extraction_method' => $fileMetadata->extraction_method,
        ]);
    }
}
