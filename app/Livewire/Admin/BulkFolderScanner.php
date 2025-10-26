<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\FolderScanJob;
use App\Services\FolderScanService;
use Livewire\Attributes\On;
use Livewire\Component;

class BulkFolderScanner extends Component
{
    public string $folderPath = '';

    public bool $recursive = true;

    public array $fileFormatFilters = ['pdf', 'epub', 'txt', 'docx'];

    public ?int $maxDepth = null;

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
            $this->currentScanJob = $folderScanService->initiateScan(
                $this->folderPath,
                [
                    'recursive' => $this->recursive,
                    'file_format_filters' => $this->fileFormatFilters,
                    'max_depth' => $this->maxDepth,
                ],
                auth()->id()
            );

            session()->flash('message', __('Bulk scan started. Discovering files...'));
        } catch (\Exception $e) {
            session()->flash('error', __('Error: ').$e->getMessage());
        }
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

    public function render()
    {
        return view('livewire.admin.bulk-folder-scanner');
    }
}
