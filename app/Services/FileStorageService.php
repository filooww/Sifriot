<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FileRegistrationLog;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = config('library.storage.base_path');
    }

    /**
     * Browse folder and return folders and files with metadata
     */
    public function browseFolder(string $relativePath): array
    {
        $path = $relativePath ?: '';

        $directories = Storage::disk('library')->directories($path);
        $files = Storage::disk('library')->files($path);

        $folders = [];
        foreach ($directories as $directory) {
            $folders[] = [
                'path' => $directory,
                'name' => basename($directory),
            ];
        }

        $fileList = [];
        foreach ($files as $file) {
            $fullPath = Storage::disk('library')->path($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            // Check registration status and source
            $registrationLog = FileRegistrationLog::where('file_path', $fullPath)->first();
            $isRegistered = $registrationLog !== null;
            $registrationSource = $isRegistered ? $registrationLog->registration_source : null;

            $fileList[] = [
                'path' => $file,
                'name' => basename($file),
                'size' => Storage::disk('library')->size($file),
                'modified_date' => Storage::disk('library')->lastModified($file),
                'extension' => $extension,
                'format_icon' => $this->getFormatIcon($extension),
                'is_registered' => $isRegistered,
                'registration_source' => $registrationSource,
            ];
        }

        return [
            'folders' => $folders,
            'files' => $fileList,
        ];
    }

    /**
     * Get file metadata from file path
     */
    public function getFileMetadata(string $filePath): array
    {
        if (! Storage::disk('library')->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($filePath, PATHINFO_FILENAME);

        // Extract suggested title from filename (remove underscores, hyphens)
        $suggestedTitle = str_replace(['_', '-'], ' ', $nameWithoutExt);
        $suggestedTitle = ucwords($suggestedTitle);

        // Determine content type from extension or path
        $contentTypeId = $this->guessContentTypeFromPath($filePath);

        return [
            'suggested_title' => $suggestedTitle,
            'content_type_id' => $contentTypeId,
            'file_size' => Storage::disk('library')->size($filePath),
            'mime_type' => mime_content_type(Storage::disk('library')->path($filePath)),
        ];
    }

    /**
     * Validate file path (security check for path traversal)
     */
    public function validateFilePath(string $filePath): bool
    {
        // Check if file exists
        if (! Storage::disk('library')->exists($filePath)) {
            return false;
        }

        // Security check: ensure path is within allowed base path
        $realPath = Storage::disk('library')->path($filePath);
        $basePath = Storage::disk('library')->path('');

        // Prevent path traversal attacks
        if (strpos($realPath, $basePath) !== 0) {
            throw new \Exception('Invalid file path: path traversal detected');
        }

        return true;
    }

    /**
     * Get file content
     */
    public function getFileContent(string $filePath): string
    {
        try {
            return Storage::disk('library')->get($filePath);
        } catch (\Exception) {
            throw new \Exception("Unable to read file: {$filePath}");
        }
    }

    /**
     * Get format icon based on file extension
     */
    protected function getFormatIcon(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'document-text',
            'epub' => 'book-open',
            'txt' => 'document',
            'docx', 'doc' => 'document',
            default => 'document',
        };
    }

    /**
     * Guess content type from file path and extension
     */
    protected function guessContentTypeFromPath(string $filePath): ?int
    {
        // Try to determine from path structure
        if (str_contains($filePath, '/books/') || str_contains($filePath, '/Books/')) {
            return 1; // Books
        }
        if (str_contains($filePath, '/magazines/') || str_contains($filePath, '/Magazines/')) {
            return 2; // Magazines
        }
        if (str_contains($filePath, '/articles/') || str_contains($filePath, '/Articles/')) {
            return 3; // Articles
        }

        // Default to 'Other'
        return 4;
    }

    /**
     * Get configured library path
     */
    public function getConfiguredPath(): string
    {
        return config('library.storage.library_path');
    }

    /**
     * Check if file path is external (vs internal storage)
     */
    public function isExternalPath(string $filePath): bool
    {
        $internalStoragePath = Storage::disk('local')->path('content');
        $realPath = realpath($filePath) ?: $filePath;

        return strpos($realPath, $internalStoragePath) !== 0;
    }
}
