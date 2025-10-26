<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\LibraryPath;
use App\Services\FileStorageService;
use Livewire\Component;

class FolderTreeNavigation extends Component
{
    public array $rootPaths = [];

    public array $expandedPaths = [];

    public ?string $selectedPath = null;

    public array $childFolders = [];

    public function mount(): void
    {
        // Load all active library paths as root nodes
        $this->rootPaths = LibraryPath::active()
            ->get()
            ->map(function (LibraryPath $path) {
                return [
                    'path' => $path->path,
                    'label' => $path->label,
                    'id' => $path->id,
                ];
            })
            ->toArray();
    }

    public function expandFolder(string $path): void
    {
        if (! in_array($path, $this->expandedPaths)) {
            $this->expandedPaths[] = $path;
        }

        // Load child folders using injected service
        try {
            $fileStorageService = app(FileStorageService::class);
            $folderContents = $fileStorageService->browseFolder($path);
            $this->childFolders[$path] = $folderContents['folders'];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to expand folder', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
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
