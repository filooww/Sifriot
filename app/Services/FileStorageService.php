<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FileStorageServiceInterface;
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
     */
    public function download(string $disk, string $path, string $filename)
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
}
