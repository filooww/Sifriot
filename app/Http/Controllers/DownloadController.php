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
        // Find the file by composite key
        $file = File::where('id_publication', $publication)
            ->where('file_name', $filename)
            ->firstOrFail();

        // Get the file path from storage
        $filePath = 'content/'.$file->file_source;

        // Check if file exists in storage
        if (! Storage::exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        // Log download event
        Log::info('File downloaded', [
            'publication_id' => $publication,
            'file_name' => $filename,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        // Download the file
        return Storage::download($filePath, $filename);
    }
}
