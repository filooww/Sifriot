<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\FileMetadata;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MetadataReviewDashboard extends Component
{
    use WithPagination;

    // Search
    #[Url]
    public string $search = '';

    // Metadata filtering
    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $formatFilter = 'all';

    #[Url]
    public string $dateFilter = 'all';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    // Publication filtering (from PublicationFilters)
    #[Url]
    public array $filterCategories = [];

    #[Url]
    public array $filterAuthors = [];

    #[Url]
    public ?string $filterDateFrom = null;

    #[Url]
    public ?string $filterDateTo = null;

    #[Url]
    public array $filterGenres = [];

    #[Url]
    public array $filterTextSizeRange = [0, 500000];

    #[Url]
    public ?string $filterAlphabeticalSort = null;

    #[Url]
    public array $filterPublicationStatus = [];

    // Selection management
    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?int $selectedMetadataId = null;

    public int $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'formatFilter' => ['except' => 'all'],
        'dateFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'filterCategories' => ['except' => []],
        'filterAuthors' => ['except' => []],
        'filterDateFrom' => ['except' => null],
        'filterDateTo' => ['except' => null],
        'filterGenres' => ['except' => []],
        'filterTextSizeRange' => ['except' => [0, 500000]],
        'filterAlphabeticalSort' => ['except' => null],
        'filterPublicationStatus' => ['except' => []],
    ];

    /**
     * Update search and reset pagination
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Listen to filter changes from PublicationFilters component
     */
    #[On('filtersChanged')]
    public function applyFilters(array $filters): void
    {
        $this->filterCategories = $filters['categories'] ?? [];
        $this->filterAuthors = $filters['authors'] ?? [];
        $this->filterDateFrom = $filters['dateFrom'] ?? null;
        $this->filterDateTo = $filters['dateTo'] ?? null;
        $this->filterGenres = $filters['genres'] ?? [];
        $this->filterTextSizeRange = $filters['textSizeRange'] ?? [0, 500000];
        $this->filterAlphabeticalSort = $filters['alphabeticalSort'] ?? null;
        $this->filterPublicationStatus = $filters['publicationStatus'] ?? [];

        $this->resetPage();
    }

    /**
     * Listen to search updates from GlobalSearch component
     */
    #[On('searchUpdated')]
    public function updateSearch(string $searchQuery): void
    {
        $this->search = $searchQuery;
        $this->resetPage();
    }

    /**
     * Listen for metadata confirmation/refresh
     */
    #[On('refresh-metadata-queue')]
    public function refreshQueue(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->formatFilter = 'all';
        $this->dateFilter = 'all';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->filterCategories = [];
        $this->filterAuthors = [];
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->filterGenres = [];
        $this->filterTextSizeRange = [0, 500000];
        $this->filterAlphabeticalSort = null;
        $this->filterPublicationStatus = [];
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();
        $this->dispatch('notify', message: 'Filters cleared', type: 'success');
    }

    /**
     * Get statistics for metadata statuses
     */
    private function getStats(): array
    {
        $baseQuery = FileMetadata::query();

        return [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processed' => (clone $baseQuery)->where('status', 'processed')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Get paginated file metadata list with all filters applied
     */
    private function getFileMetadataList()
    {
        $query = FileMetadata::query();

        // Text search - search in file names and extracted data
        if ($this->search) {
            $searchTerm = trim($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('file_name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('extracted_data->title', 'like', '%'.$searchTerm.'%');
            });
        }

        // Metadata status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Format filter
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

        // Apply publication filters - file_id actually stores publication ID
        // Publication category filter
        if (! empty($this->filterCategories)) {
            $query->whereHas('publication.categories', fn ($q) => $q->whereIn('categories.id', $this->filterCategories));
        }

        // Publication author filter
        if (! empty($this->filterAuthors)) {
            $query->whereHas('publication.authors', fn ($q) => $q->whereIn('authors.id_author', $this->filterAuthors));
        }

        // Publication date range filter
        if ($this->filterDateFrom && $this->filterDateTo) {
            $query->whereHas('publication', fn ($q) => $q->whereBetween('upload_date', [$this->filterDateFrom, $this->filterDateTo]));
        } elseif ($this->filterDateFrom) {
            $query->whereHas('publication', fn ($q) => $q->where('upload_date', '>=', $this->filterDateFrom));
        } elseif ($this->filterDateTo) {
            $query->whereHas('publication', fn ($q) => $q->where('upload_date', '<=', $this->filterDateTo));
        }

        // Publication genre filter
        if (! empty($this->filterGenres)) {
            $query->whereHas('publication.genres', fn ($q) => $q->whereIn('genres.id', $this->filterGenres));
        }

        // Publication text size filter
        if ($this->filterTextSizeRange !== [0, 500000]) {
            $query->whereHas('publication', fn ($q) => $q->whereBetween('word_count', [$this->filterTextSizeRange[0], $this->filterTextSizeRange[1]]));
        }

        // Publication status filter
        if (! empty($this->filterPublicationStatus)) {
            $query->whereHas('publication', fn ($q) => $q->whereIn('status', $this->filterPublicationStatus));
        }

        // Eager load publication relationship
        $query->with('publication');

        // Apply sorting with qualified column names to avoid ambiguity
        $qualifiedColumn = in_array($this->sortBy, ['created_at', 'updated_at'])
            ? "file_metadatas.{$this->sortBy}"
            : $this->sortBy;
        $query->orderBy($qualifiedColumn, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Change sort column
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
     * Toggle select all
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
     * Toggle individual item selection
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
     * Confirm all selected metadata items
     */
    public function confirmAllSelected(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', message: 'No items selected', type: 'warning');
            return;
        }

        FileMetadata::whereIn('id', $this->selectedItems)
            ->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

        $count = count($this->selectedItems);
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();
        $this->dispatch('notify', message: "{$count} items confirmed", type: 'success');
    }

    /**
     * Reject all selected metadata items
     */
    public function rejectAllSelected(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', message: 'No items selected', type: 'warning');
            return;
        }

        FileMetadata::whereIn('id', $this->selectedItems)
            ->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

        $count = count($this->selectedItems);
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();
        $this->dispatch('notify', message: "{$count} items rejected", type: 'success');
    }

    /**
     * Re-extract all selected metadata items
     */
    public function reExtractSelected(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', message: 'No items selected', type: 'warning');
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
        $this->resetPage();
        $this->dispatch('notify', message: "{$count} items queued for re-extraction", type: 'success');
    }

    /**
     * Re-extract a single metadata record
     */
    public function reExtractSingle(int $id): void
    {
        $metadata = FileMetadata::find($id);
        if ($metadata && $metadata->file_id) {
            ExtractMetadataFromFile::dispatch($metadata->file_id, $metadata->file_name, 1);
            $this->dispatch('notify', message: 'Re-extraction queued', type: 'success');
        }
    }

    /**
     * Delete a metadata record
     */
    public function deleteMetadata(int $id): void
    {
        FileMetadata::find($id)?->delete();
        $this->dispatch('notify', message: 'Metadata deleted', type: 'success');
    }

    /**
     * Toggle publication status
     */
    public function togglePublicationStatus(int $publicationId, string $newStatus): void
    {
        if (! in_array($newStatus, ['published', 'hidden', 'pending'])) {
            $this->dispatch('notify', message: 'Invalid status', type: 'error');
            return;
        }

        try {
            $publication = Publication::find($publicationId);
            if ($publication) {
                $oldStatus = $publication->status;
                $publication->update(['status' => $newStatus]);

                Log::channel('folder_scan')->info("Publication {$publicationId} status changed from {$oldStatus} to {$newStatus}", [
                    'admin_id' => auth()->id(),
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);

                $this->dispatch('notify', message: "Status updated to {$newStatus}", type: 'success');
            }
        } catch (\Exception $e) {
            Log::error('Failed to toggle publication status', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'Failed to update status', type: 'error');
        }
    }

    /**
     * Bulk update publication status
     */
    public function bulkUpdateStatus(array $publicationIds, string $newStatus): void
    {
        if (empty($publicationIds) || ! in_array($newStatus, ['published', 'hidden', 'pending'])) {
            $this->dispatch('notify', message: 'Invalid request', type: 'error');
            return;
        }

        try {
            DB::transaction(function () use ($publicationIds, $newStatus) {
                Publication::whereIn('id_publication', $publicationIds)
                    ->update(['status' => $newStatus]);

                Log::channel('folder_scan')->info("Bulk status update: {$newStatus}", [
                    'admin_id' => auth()->id(),
                    'count' => count($publicationIds),
                    'publication_ids' => $publicationIds,
                ]);
            });

            $this->dispatch('notify', message: count($publicationIds)." publications updated to {$newStatus}", type: 'success');
        } catch (\Exception $e) {
            Log::error('Bulk status update failed', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'Bulk update failed', type: 'error');
        }
    }

    /**
     * Get status badge info
     */
    public function getStatusBadge(string $status): array
    {
        return match ($status) {
            'pending' => ['icon' => '⏳', 'label' => 'Pending', 'color' => 'yellow'],
            'processed' => ['icon' => '📋', 'label' => 'Ready for Review', 'color' => 'blue'],
            'confirmed' => ['icon' => '✅', 'label' => 'Confirmed', 'color' => 'green'],
            'failed' => ['icon' => '❌', 'label' => 'Failed', 'color' => 'red'],
            'rejected' => ['icon' => '🚫', 'label' => 'Rejected', 'color' => 'gray'],
            default => ['icon' => '?', 'label' => $status, 'color' => 'gray'],
        };
    }

    /**
     * Get publication status badge
     */
    public function getPublicationStatusBadge(string $status): array
    {
        return match ($status) {
            'published' => ['icon' => '🌐', 'label' => 'Published', 'color' => 'green'],
            'hidden' => ['icon' => '🔒', 'label' => 'Hidden', 'color' => 'red'],
            'pending' => ['icon' => '⏳', 'label' => 'Pending', 'color' => 'yellow'],
            default => ['icon' => '?', 'label' => $status, 'color' => 'gray'],
        };
    }

    /**
     * Get file extension
     */
    public function getFileExtension(string $filename): string
    {
        return strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
    }

    public function render()
    {
        return view('livewire.admin.metadata-review-dashboard', [
            'fileMetadataList' => $this->getFileMetadataList(),
            'stats' => $this->getStats(),
        ]);
    }
}
