<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\FileMetadata;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MetadataReviewQueue extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $formatFilter = 'all';

    public string $dateFilter = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?int $selectedMetadataId = null;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'formatFilter' => ['except' => 'all'],
        'dateFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    /**
     * Get the base query for file metadata.
     */
    private function getBaseQuery()
    {
        return FileMetadata::query();
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query)
    {
        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Format filter (from filename extension)
        if ($this->formatFilter !== 'all') {
            $extension = strtolower($this->formatFilter);
            $query->whereRaw("LOWER(SUBSTRING_INDEX(file_name, '.', -1)) = ?", [$extension]);
        }

        // Date filter
        if ($this->dateFilter !== 'all') {
            $days = match ($this->dateFilter) {
                '1day' => 1,
                '7days' => 7,
                '30days' => 30,
                default => null,
            };

            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        return $query;
    }

    /**
     * Get statistics.
     */
    public function getStats(): array
    {
        $query = $this->getBaseQuery();

        return [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'processed' => (clone $query)->where('status', 'processed')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get queue processing statistics.
     */
    public function getQueueStats(): array
    {
        $total = FileMetadata::count();
        $pending = FileMetadata::where('status', 'pending')->count();
        $processing = FileMetadata::where('status', 'processing')->count();
        $completed = FileMetadata::whereIn('status', ['processed', 'confirmed'])->count();

        $inProgress = $pending + $processing;
        $percentComplete = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'percent_complete' => $percentComplete,
            'is_processing' => $inProgress > 0,
        ];
    }

    /**
     * Get paginated file metadata list.
     */
    public function getFileMetadataList()
    {
        $query = $this->getBaseQuery();
        $query = $this->applyFilters($query);

        return $query
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    /**
     * Listen for metadata confirmation from child component.
     */
    #[On('refresh-metadata-queue')]
    public function refreshQueue(): void
    {
        $this->resetPage();
    }

    /**
     * Change sort column.
     */
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    /**
     * Toggle select all.
     */
    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedItems = $this->getFileMetadataList()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    /**
     * Toggle item selection.
     */
    public function toggleItem(int $id): void
    {
        $idString = (string) $id;

        if (in_array($idString, $this->selectedItems)) {
            $this->selectedItems = array_filter(
                $this->selectedItems,
                fn ($item) => $item !== $idString
            );
        } else {
            $this->selectedItems[] = $idString;
        }

        $this->selectAll = false;
    }

    /**
     * Confirm all selected items.
     */
    public function confirmAllSelected(): void
    {
        if (empty($this->selectedItems)) {
            return;
        }

        FileMetadata::whereIn('id', $this->selectedItems)
            ->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('notify', message: count($this->selectedItems).' items confirmed', type: 'success');
    }

    /**
     * Reject all selected items.
     */
    public function rejectAllSelected(): void
    {
        if (empty($this->selectedItems)) {
            return;
        }

        FileMetadata::whereIn('id', $this->selectedItems)
            ->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('notify', message: count($this->selectedItems).' items rejected', type: 'success');
    }

    /**
     * Re-extract selected items.
     */
    public function reExtractSelected(): void
    {
        if (empty($this->selectedItems)) {
            return;
        }

        $count = 0;
        foreach ($this->selectedItems as $id) {
            $metadata = FileMetadata::find($id);
            if ($metadata && $metadata->file_id) {
                ExtractMetadataFromFile::dispatch($metadata->file_id, $metadata->file_name, 1);
                $count++;
            }
        }

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('notify', message: "{$count} items queued for re-extraction", type: 'success');
    }

    /**
     * Re-extract a single metadata record.
     */
    public function reExtractSingle(int $id): void
    {
        $metadata = FileMetadata::find($id);
        if ($metadata && $metadata->file_id) {
            // Get file path from files table
            [$publicationId, $fileName] = explode('-', $metadata->file_id, 2);
            $file = \App\Models\File::where('id_publication', $publicationId)
                ->where('file_name', $fileName)
                ->first();

            if ($file) {
                $filePath = $file->file_source.'/'.$file->file_name;
                $fullPath = \Storage::disk('library')->path($filePath);

                ExtractMetadataFromFile::dispatch(
                    $metadata->file_id,
                    $fullPath,
                    $file->publication->content_type_id ?? 1,
                    $file->mime_type ?? 'application/octet-stream'
                );

                $this->dispatch('notify', message: 'Re-extraction queued', type: 'success');
            }
        }
    }

    /**
     * Delete a metadata record.
     */
    public function deleteMetadata(int $id): void
    {
        FileMetadata::find($id)?->delete();
        $this->dispatch('notify', message: 'Metadata deleted', type: 'success');
    }

    /**
     * Get status badge info.
     */
    public function getStatusBadge(string $status): array
    {
        return match ($status) {
            'pending' => ['icon' => '⏳', 'label' => 'Pending', 'color' => 'yellow'],
            'processing' => ['icon' => '🔄', 'label' => 'Processing', 'color' => 'blue'],
            'processed' => ['icon' => '📋', 'label' => 'Ready for Review', 'color' => 'blue'],
            'confirmed' => ['icon' => '✅', 'label' => 'Confirmed', 'color' => 'green'],
            'failed' => ['icon' => '❌', 'label' => 'Failed', 'color' => 'red'],
            'rejected' => ['icon' => '🚫', 'label' => 'Rejected', 'color' => 'gray'],
            default => ['icon' => '?', 'label' => $status, 'color' => 'gray'],
        };
    }

    /**
     * Get file extension from filename.
     */
    public function getFileExtension(string $filename): string
    {
        return strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
    }

    public function render()
    {
        return view('livewire.admin.metadata-review-queue', [
            'fileMetadataList' => $this->getFileMetadataList(),
            'stats' => $this->getStats(),
            'queueStats' => $this->getQueueStats(),
        ]);
    }
}
