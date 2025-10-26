<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\FolderScanJob;
use App\Services\FolderScanService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class BulkFolderScanner extends Component
{
    public string $folderPath = '';

    public bool $recursive = true;

    public array $fileFormatFilters = ['pdf', 'epub', 'txt', 'docx'];

    public ?FolderScanJob $currentScanJob = null;

    public function mount(): void
    {
        // Load active scan job if exists
        $this->currentScanJob = FolderScanJob::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();
    }

    public function startScan(FolderScanService $folderScanService): void
    {
        $this->validate([
            'folderPath' => 'required|string',
        ]);

        try {
            // Convert absolute path to relative path for storage operations
            $libStoragePath = config('filesystems.disks.library.root');
            $relativePath = $this->getRelativePath($this->folderPath, $libStoragePath);

            $this->currentScanJob = $folderScanService->initiateScan(
                $relativePath,
                [
                    'recursive' => $this->recursive,
                    'file_format_filters' => $this->fileFormatFilters,
                ],
                auth()->id()
            );

            session()->flash('message', __('Bulk scan started. Discovering files...'));
        } catch (\Exception $e) {
            session()->flash('error', __('Error: ').$e->getMessage());
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

        // Return as-is if not under storage path
        return $absolutePath;
    }

    public function refreshProgress(): void
    {
        if ($this->currentScanJob) {
            $this->currentScanJob->refresh();

            $this->dispatch('scan-progress-updated', [
                'progress' => $this->currentScanJob->progress_percent,
                'files_registered' => $this->currentScanJob->files_registered,
                'files_skipped' => $this->currentScanJob->files_skipped,
                'files_failed' => $this->currentScanJob->files_failed,
                'total_files_found' => $this->currentScanJob->total_files_found,
            ]);
        }
    }

    public function pauseScan(FolderScanService $folderScanService): void
    {
        if ($this->currentScanJob) {
            $folderScanService->pauseScan($this->currentScanJob);
            $this->currentScanJob->refresh();
            session()->flash('message', __('Scan paused'));
        }
    }

    public function cancelScan(FolderScanService $folderScanService): void
    {
        if ($this->currentScanJob) {
            $folderScanService->cancelScan($this->currentScanJob);
            $this->currentScanJob->refresh();
            session()->flash('message', __('Scan cancelled'));
        }
    }

    #[On('folder-selected')]
    public function onFolderSelected(string $path): void
    {
        $this->folderPath = $path;
    }

    #[On('bulk-scan-requested')]
    public function onBulkScanRequested(string $folderPath): void
    {
        $this->folderPath = $folderPath;
    }

    public function render()
    {
        return view('livewire.admin.bulk-folder-scanner');
    }
}
