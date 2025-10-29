<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Download a file.
     */
    public function download(int $publication, string $filename): Response|StreamedResponse
    {
        // Decode URL-safe base64-encoded filename (handles Cyrillic characters)
        // Add back padding and convert URL-safe characters back to standard base64
        $base64 = str_pad(strtr($filename, '-_', '+/'), strlen($filename) % 4, '=', STR_PAD_RIGHT);
        $decodedFilename = base64_decode($base64, true);

        if ($decodedFilename === false) {
            abort(400, 'Invalid filename encoding');
        }

        // Find the file by composite key
        $file = File::where('id_publication', $publication)
            ->where('file_name', $decodedFilename)
            ->firstOrFail();

        // Get the file path from storage
        // Use 'library' disk for bulk scanned files, 'local' disk for uploaded files
        $fileSource = $file->file_source;

        // Check if file_source contains a full path (uploaded file) or directory path (bulk scanned)
        if (pathinfo($fileSource, PATHINFO_EXTENSION)) {
            // Uploaded file: has an extension, use local disk with content/ prefix if not already included
            $disk = 'local';
            $filePath = str_starts_with($fileSource, 'content/') ? $fileSource : 'content/' . $fileSource;
        } elseif ($fileSource === 'bulk_scan') {
            // Legacy bulk_scan files: search recursively in library disk (for backwards compatibility)
            $disk = 'library';

            // Search for the file in the library directory
            $allFiles = Storage::disk($disk)->allFiles();
            $filePath = null;

            foreach ($allFiles as $path) {
                if (basename($path) === $decodedFilename) {
                    $filePath = $path;
                    break;
                }
            }

            if ($filePath === null) {
                Log::error('Bulk scanned file not found in library for download', [
                    'publication_id' => $publication,
                    'filename' => $decodedFilename,
                    'searched_in' => Storage::disk($disk)->path(''),
                ]);
                abort(404, 'File not found in library storage');
            }
        } else {
            // New bulk scanned file: file_source is the relative directory path on library disk
            $disk = 'library';
            $filePath = $fileSource . '/' . $decodedFilename;
        }

        // Check if file exists in storage
        if (! Storage::disk($disk)->exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        // Log download event
        Log::info('File downloaded', [
            'publication_id' => $publication,
            'file_name' => $decodedFilename,
            'disk' => $disk,
            'file_path' => $filePath,
            'user_id' => auth()->user()?->id,
            'ip' => request()->ip(),
        ]);

        // Download the file with the original filename
        return Storage::disk($disk)->download($filePath, $decodedFilename);
    }
}
