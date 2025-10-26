<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use App\Models\Publication;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ScanResultsViewer extends Component
{
    use WithPagination;

    public ?int $scanJobId = null;

    public ?string $filterStatus = null;

    public ?FolderScanJob $scanJob = null;

    public function mount(?int $scanJobId = null): void
    {
        $this->scanJobId = $scanJobId;
        if ($scanJobId) {
            $this->scanJob = FolderScanJob::findOrFail($scanJobId);
        }
    }

    #[On('scan-job-created')]
    public function onScanJobCreated(int $scanJobId): void
    {
        $this->scanJobId = $scanJobId;
        $this->scanJob = FolderScanJob::findOrFail($scanJobId);
        $this->resetPage();
        $this->filterStatus = null;
    }

    public function setFilter(?string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function getResultsProperty()
    {
        if (! $this->scanJobId) {
            return collect();
        }

        $query = FileRegistrationLog::where('folder_scan_job_id', $this->scanJobId);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    public function bulkApprove(): void
    {
        $publicationIds = FileRegistrationLog::where('folder_scan_job_id', $this->scanJobId)
            ->where('status', 'processed')
            ->whereNotNull('publication_id')
            ->pluck('publication_id');

        $count = Publication::whereIn('id_publication', $publicationIds)
            ->where('status', 'pending')
            ->update(['status' => 'published']);

        session()->flash('message', __(':count publications approved and published', ['count' => $count]));
    }

    public function render()
    {
        return view('livewire.admin.scan-results-viewer', [
            'results' => $this->results,
        ]);
    }
}
