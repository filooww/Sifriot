<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\FileStorageServiceInterface;
use App\Contracts\LoggerServiceInterface;
use App\Models\File;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download Controller
 *
 * This controller demonstrates the proper use of Dependency Injection.
 * Notice how we inject services through the constructor instead of using facades.
 * This makes the controller:
 * - Easier to test (we can pass mock services)
 * - Clearer about its dependencies (you can see what it needs)
 * - Less tightly coupled to specific implementations
 */
class DownloadController extends Controller
{
    /**
     * Constructor - Dependency Injection in action
     *
     * Laravel's Service Container automatically creates these services
     * and passes them to the constructor based on the type hints.
     *
     * @param FileStorageServiceInterface $storage The file storage service
     * @param LoggerServiceInterface $logger The logger service
     */
    public function __construct(
        private FileStorageServiceInterface $storage,
        private LoggerServiceInterface $logger,
    ) {}

    /**
     * Download a file.
     *
     * Note: The logic here is the same, but now it uses injected services
     * instead of facades. This is much more testable and maintainable.
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
            // Using $this->storage instead of Storage facade
            $allFiles = $this->storage->allFiles($disk);
            $filePath = null;

            foreach ($allFiles as $path) {
                if (basename($path) === $decodedFilename) {
                    $filePath = $path;
                    break;
                }
            }

            if ($filePath === null) {
                // Using $this->logger instead of Log facade
                $this->logger->error('Bulk scanned file not found in library for download', [
                    'publication_id' => $publication,
                    'filename' => $decodedFilename,
                    'searched_in' => $this->storage->path($disk, ''),
                ]);
                abort(404, 'File not found in library storage');
            }
        } else {
            // New bulk scanned file: file_source is the relative directory path on library disk
            $disk = 'library';
            $filePath = $fileSource . '/' . $decodedFilename;
        }

        // Check if file exists in storage
        // Using $this->storage instead of Storage facade
        if (! $this->storage->exists($disk, $filePath)) {
            abort(404, 'File not found in storage');
        }

        // Log download event
        // Using $this->logger instead of Log facade
        $this->logger->info('File downloaded', [
            'publication_id' => $publication,
            'file_name' => $decodedFilename,
            'disk' => $disk,
            'file_path' => $filePath,
            'user_id' => auth()->user()?->id,
            'ip' => request()->ip(),
        ]);

        // Download the file with the original filename
        // Using $this->storage instead of Storage facade
        return $this->storage->download($disk, $filePath, $decodedFilename);
    }
}
