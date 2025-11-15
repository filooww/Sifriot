<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * File Storage Service Interface
 *
 * Defines a contract for file storage operations.
 * This allows for different storage implementations (local, S3, etc.)
 * and makes testing easier through dependency injection.
 */
interface FileStorageServiceInterface
{
    /**
     * Get file content from storage
     *
     * @param string $disk The storage disk name (local, library, etc.)
     * @param string $path The path to the file
     * @return string The file content
     *
     * @throws \Exception If file doesn't exist or cannot be read
     */
    public function get(string $disk, string $path): string;

    /**
     * Check if file exists in storage
     */
    public function exists(string $disk, string $path): bool;

    /**
     * Download file from storage
     *
     * @param string $disk The storage disk name
     * @param string $path The path to the file
     * @param string $filename The filename to download as
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $disk, string $path, string $filename);

    /**
     * Get the full filesystem path for a file
     */
    public function path(string $disk, string $path): string;

    /**
     * Get all files from a disk recursively
     *
     * @param string $disk The storage disk name
     * @return array<int, string> Array of file paths
     */
    public function allFiles(string $disk): array;
}
