<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\FileStorageService;
use Livewire\Component;

// This is an embedded component, not a page component
// It will be embedded in BulkFolderScanner via @livewire directive
class FolderTreeNavigation extends Component
{
    public array $rootPaths = [];

    public array $expandedPaths = [];

    public ?string $selectedPath = null;

    public array $childFolders = [];

    public function mount(): void
    {
        // Load the single library path as root node
        $libraryPath = config('library.storage.library_path');
        $this->rootPaths = [
            [
                'path' => $libraryPath,
                'label' => __('Library'),
            ],
        ];
    }

    public function expandFolder(string $path): void
    {
        if (! in_array($path, $this->expandedPaths)) {
            $this->expandedPaths[] = $path;
        }

        // Load child folders using injected service
        try {
            $fileStorageService = app(FileStorageService::class);

            // Convert absolute path to relative path for storage disk
            // The library disk root is typically /library (set in LIBRARY_STORAGE_PATH)
            // So absolute paths like /library need to be converted to empty string
            $libStoragePath = config('filesystems.disks.library.root');
            $relativePath = $this->getRelativePath($path, $libStoragePath);

            $folderContents = $fileStorageService->browseFolder($relativePath);
            $this->childFolders[$path] = $folderContents['folders'];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to expand folder', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convert absolute path to relative path for storage disk
     */
    private function getRelativePath(string $absolutePath, string $storagePath): string
    {
        // Remove trailing slashes for comparison
        $storagePath = rtrim($storagePath, '/');
        $absolutePath = rtrim($absolutePath, '/');

        // If paths are equal, return empty string (root)
        if ($absolutePath === $storagePath) {
            return '';
        }

        // If path starts with storage path, extract relative part
        if (strpos($absolutePath, $storagePath) === 0) {
            $relative = substr($absolutePath, strlen($storagePath) + 1);

            return $relative ?: '';
        }

        // Return as-is if not under storage path (this shouldn't happen in normal use)
        return $absolutePath;
    }

    public function collapseFolder(string $path): void
    {
        $this->expandedPaths = array_filter(
            $this->expandedPaths,
            fn ($p) => $p !== $path
        );

        unset($this->childFolders[$path]);
    }

    public function selectFolder(string $path): void
    {
        $this->selectedPath = $path;

        // Dispatch event to BulkFolderScanner component
        $this->dispatch('folder-selected', path: $path);
    }

    public function render()
    {
        return view('livewire.admin.folder-tree-navigation', [
            'rootPaths' => $this->rootPaths,
            'expandedPaths' => $this->expandedPaths,
            'selectedPath' => $this->selectedPath,
            'childFolders' => $this->childFolders,
        ]);
    }
}
