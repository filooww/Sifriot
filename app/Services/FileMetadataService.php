<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FileMetadata;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FileMetadataService
{
    /**
     * Ensure a publication has a corresponding FileMetadata record.
     * If not, create one in 'pending' status using the primary file.
     */
    public function syncMetadataForPublication(Publication $publication): ?FileMetadata
    {
        // 1. Check if metadata already exists
        // We look for any FileMetadata where the file_id starts with "{$publication->id}-"
        // Note: This relies on the convention that file_id = "pubID-filename"
        
        // Optimize: Check if any metadata exists for this publication ID prefix
        $exists = FileMetadata::where('file_id', 'like', "{$publication->id_publication}-%")->exists();
        
        if ($exists) {
            return null; // Already exists, nothing to do
        }

        // 2. Find the primary content file
        // We prioritize 'content' type files, then take the first available if not found
        $file = $publication->files()
            ->where('file_type', 'content') // Assuming 'content' is the main type
            ->orderBy('created_at', 'desc')
            ->first();

        // Fallback to any file if no specific 'content' file (some pubs might just have arbitrary files)
        if (!$file) {
            $file = $publication->files()->orderBy('created_at', 'desc')->first();
        }

        if (!$file) {
            Log::warning("Cannot sync metadata for publication {$publication->id_publication}: No files found.");
            return null;
        }

        // 3. Create the missing metadata record
        $fileId = "{$publication->id_publication}-{$file->file_name}";
        
        Log::info("Creating missing metadata for publication {$publication->id_publication}", [
            'file_id' => $fileId,
            'file_name' => $file->file_name
        ]);

        return FileMetadata::create([
            'file_id' => $fileId,
            'file_name' => $file->file_name,
            'status' => 'pending',
            'extracted_data' => [], // Empty initially
            'extraction_method' => 'manual_sync', // Marker to know how it was created
        ]);
    }

    /**
     * Bulk sync for a collection of publications
     */
    public function syncMetadataForPublications($publications): int
    {
        $count = 0;
        foreach ($publications as $publication) {
            if ($this->syncMetadataForPublication($publication)) {
                $count++;
            }
        }
        return $count;
    }
}
