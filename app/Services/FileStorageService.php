<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FileStorageServiceInterface;
use App\Models\FileRegistrationLog;
use Illuminate\Support\Facades\Storage;

/**
 * File Storage Service
 *
 * Implementation of FileStorageServiceInterface that wraps Laravel's Storage facade.
 * This abstraction allows us to:
 * - Test controllers without mocking facades
 * - Switch storage implementations easily
 * - Centralize storage logic in one place
 */
class FileStorageService implements FileStorageServiceInterface
{
    /**
     * Get file content from storage
     */
    public function get(string $disk, string $path): string
    {
        return Storage::disk($disk)->get($path);
    }

    /**
     * Check if file exists in storage
     */
    public function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Download file from storage
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $disk, string $path, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Get the full filesystem path for a file
     */
    public function path(string $disk, string $path): string
    {
        return Storage::disk($disk)->path($path);
    }

    /**
     * Get all files from a disk recursively
     */
    public function allFiles(string $disk): array
    {
        return Storage::disk($disk)->allFiles();
    }

    /**
     * Browse folder contents
     *
     * Returns folders and files in a given directory with metadata
     *
     * @param string $relativePath The relative path to browse (from library base path)
     * @return array Array with 'folders' and 'files' keys containing directory contents
     *
     * @throws \Exception If path doesn't exist or is inaccessible
     */
    public function browseFolder(string $relativePath): array
    {
        $disk = Storage::disk('library');

        // Get directories and files
        $folders = $disk->directories($relativePath);
        $files = $disk->files($relativePath);

        // Build folder array with basic info
        $folderList = array_map(function ($folderPath) {
            $folderName = basename($folderPath);

            return [
                'path' => $folderPath,
                'name' => $folderName,
            ];
        }, $folders);

        // Build file array with metadata
        $fileList = array_map(function ($filePath) {
            $fileName = basename($filePath);
            $fullPath = Storage::disk('library')->path($filePath);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Check if file is registered and get registration source
            $registrationLog = FileRegistrationLog::where('file_path', $fullPath)->first();
            $isRegistered = $registrationLog !== null;
            $registrationSource = $registrationLog?->registration_source ?? null;

            // Format icon mapping
            $formatIcons = [
                'pdf' => 'document-text',
                'epub' => 'book-open',
                'txt' => 'document',
                'docx' => 'document',
                'doc' => 'document',
            ];

            return [
                'path' => $filePath,
                'name' => $fileName,
                'size' => filesize($fullPath) ?? 0,
                'modified_date' => filemtime($fullPath) ?? 0,
                'extension' => $extension,
                'is_registered' => $isRegistered,
                'registration_source' => $registrationSource,
                'format_icon' => $formatIcons[$extension] ?? 'document',
            ];
        }, $files);

        return [
            'folders' => $folderList,
            'files' => $fileList,
        ];
    }

    /**
     * Get file metadata from filename
     *
     * Extracts suggested metadata from a file path
     *
     * @param string $filePath The file path
     * @return array Array with 'suggested_title', 'content_type_id', 'file_size', 'mime_type'
     *
     * @throws \Exception If file doesn't exist
     */
    public function getFileMetadata(string $filePath): array
    {
        $disk = Storage::disk('library');

        // Verify file exists
        if (!$disk->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fileName = basename($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fullPath = $disk->path($filePath);
        $fileSize = filesize($fullPath) ?? 0;

        // Extract title from filename (remove extension and replace underscores/hyphens with spaces)
        $suggestedTitle = pathinfo($fileName, PATHINFO_FILENAME);
        $suggestedTitle = preg_replace('/[-_]/', ' ', $suggestedTitle) ?? $suggestedTitle;

        // Map extension to content type
        $extensionToContentType = [
            'pdf' => 1,  // Books (default)
            'epub' => 1, // Books
            'txt' => 1,  // Books
            'docx' => 1, // Books
            'doc' => 1,  // Books
        ];

        // MIME type mapping
        $extensionToMimeType = [
            'pdf' => 'application/pdf',
            'epub' => 'application/epub+zip',
            'txt' => 'text/plain',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
        ];

        return [
            'suggested_title' => $suggestedTitle,
            'content_type_id' => $extensionToContentType[$extension] ?? 1,
            'file_size' => $fileSize,
            'mime_type' => $extensionToMimeType[$extension] ?? 'application/octet-stream',
        ];
    }

    /**
     * Validate file path
     *
     * Verifies that a path exists and is within allowed directories
     *
     * @param string $filePath The file path to validate
     * @return bool True if path is valid
     *
     * @throws \Exception If path is invalid or outside allowed locations
     */
    public function validateFilePath(string $filePath): bool
    {
        // Check for path traversal attempts
        if (str_contains($filePath, '..') || str_starts_with($filePath, '/')) {
            throw new \Exception('Invalid file path: path traversal detected');
        }

        $disk = Storage::disk('library');

        // Verify path exists
        if (!$disk->exists($filePath)) {
            throw new \Exception("File path does not exist: {$filePath}");
        }

        return true;
    }
}
