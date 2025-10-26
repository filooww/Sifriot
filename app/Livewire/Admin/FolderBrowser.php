<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\LibraryPath;
use App\Services\FileStorageService;
use Livewire\Component;

class FolderBrowser extends Component
{
    public string $currentPath = '';

    public array $folders = [];

    public array $files = [];

    public array $selectedFiles = [];

    public array $libraryPaths = [];

    public int $displayLimit = 1000;

    public int $currentDisplayCount = 1000;

    protected FileStorageService $fileStorage;

    public function boot(FileStorageService $fileStorage): void
    {
        $this->fileStorage = $fileStorage;
    }

    public function mount(): void
    {
        // Load configured library paths for quick navigation
        $this->libraryPaths = LibraryPath::active()
            ->get()
            ->map(function (LibraryPath $path) {
                return [
                    'id' => $path->id,
                    'label' => $path->label,
                    'path' => $path->path,
                ];
            })
            ->toArray();

        $this->loadFolder('');
    }

    public function loadFolder(string $relativePath): void
    {
        try {
            $result = $this->fileStorage->browseFolder($relativePath);
            $this->currentPath = $relativePath;
            $this->folders = $result['folders'];

            // Apply pagination for large folders
            $allFiles = $result['files'];
            $this->files = array_slice($allFiles, 0, $this->currentDisplayCount);

        } catch (\Exception $e) {
            \Log::error('FolderBrowser error', [
                'path' => $relativePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', __('Unable to browse folder').': '.$e->getMessage());
        }
    }

    public function loadMoreFiles(): void
    {
        $this->currentDisplayCount += $this->displayLimit;
        $this->loadFolder($this->currentPath);
    }

    public function registerSelected()
    {
        if (empty($this->selectedFiles)) {
            session()->flash('error', __('Please select at least one file'));

            return;
        }

        if (count($this->selectedFiles) > 1) {
            session()->flash('error', __('Bulk registration coming in Story 1.7. Please select one file.'));

            return;
        }

        // Redirect to registration form with selected file path
        $filePath = $this->selectedFiles[0];

        return $this->redirect(route('admin.files.register', ['filePath' => $filePath]));
    }

    public function startBulkScan(): void
    {
        if (empty($this->currentPath)) {
            session()->flash('error', __('Please select a folder first'));

            return;
        }

        // Dispatch event to BulkFolderScanner to populate the folder path
        $this->dispatch('bulk-scan-requested', folderPath: $this->currentPath);
        session()->flash('message', __('Bulk scan ready. Configure options and start.'));
    }

    public function getBreadcrumbs(): array
    {
        if (empty($this->currentPath)) {
            return [['name' => __('Root'), 'path' => '']];
        }

        $segments = explode('/', $this->currentPath);
        $breadcrumbs = [['name' => __('Root'), 'path' => '']];
        $path = '';

        foreach ($segments as $segment) {
            $path .= ($path ? '/' : '').$segment;
            $breadcrumbs[] = ['name' => $segment, 'path' => $path];
        }

        return $breadcrumbs;
    }

    public function render()
    {
        return view('livewire.admin.folder-browser', [
            'breadcrumbs' => $this->getBreadcrumbs(),
            'libraryPaths' => $this->libraryPaths,
        ]);
    }
}
