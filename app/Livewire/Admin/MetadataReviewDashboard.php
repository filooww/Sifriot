<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\File;
use App\Models\FileMetadata;
use App\Models\FileRegistrationLog;
use App\Models\Publication;
use App\Services\MetadataExtractors\DocumentTextExtractor;
use App\Services\MetadataExtractors\GeminiMetadataExtractorService;
use App\Services\FileMetadataService;
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
    public array $filterSections = [];

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

    public int $perPage = 20;

    // UI state
    public bool $sidebarCollapsed = false;

    public bool $geminiConfigured = false;

    public bool $isExtractingWithAI = false;

    public bool $showOrphanedPublications = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'formatFilter' => ['except' => 'all'],
        'dateFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'filterSections' => ['except' => []],
        'filterAuthors' => ['except' => []],
        'filterDateFrom' => ['except' => null],
        'filterDateTo' => ['except' => null],
        'filterGenres' => ['except' => []],
        'filterTextSizeRange' => ['except' => [0, 500000]],
        'filterAlphabeticalSort' => ['except' => null],
        'filterPublicationStatus' => ['except' => []],
    ];

    public function mount(FileMetadataService $metadataService): void
    {
        $this->geminiConfigured = !empty(config('services.gemini.api_key'));

        // Auto-heal: Ensure all visible publications have metadata records
        // This is a "lazy" check - we could optimize to only check "missing" ones if performance is an issue,
        // but for now, we'll rely on the service to handle existence checks efficiently.
        $this->ensureMetadataExists($metadataService);

        Log::info('MetadataReviewDashboard mounted', [
            'gemini_configured' => $this->geminiConfigured,
            'api_key_set' => !empty(config('services.gemini.api_key')),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Ensure metadata exists for relevant publications
     */
    protected function ensureMetadataExists(FileMetadataService $metadataService): void
    {
        // 1. Cleanup zombies (metadata pointing to deleted publications)
        // This ensures that if a publication was deleted, its metadata is also removed
        $deletedCount = FileMetadata::whereNotExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('publications')
                ->whereRaw('publications.id_publication = CAST(SUBSTRING_INDEX(file_metadatas.file_id, "-", 1) AS UNSIGNED)')
                ->whereNull('deleted_at');
        })->delete();

        if ($deletedCount > 0) {
            Log::info("MetadataReviewDashboard: Cleaned up {$deletedCount} zombie metadata records.");
        }

        // Get IDs of publications that match current broad criteria (e.g. not deleted)
        // We limit this to recent or active ones to avoid scanning the entire DB on every load if it's huge.
        // For now, let's just check the ones that would be visible.

        // Optimization: querying ALL might be heavy. Let's query "orphans" directly here.
        // The FileMetadata.file_id is formatted as "pubID-filename".
        // The relation whereDoesntHave('fileMetadata') fails because keys don't match directly.
        // So we manually find publications IDs that are NOT present as prefixes in file_metadatas.

        $orphans = Publication::query()
            ->whereNull('deleted_at') // Explicitly exclude soft-deleted
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('file_metadatas')
                    ->whereRaw('file_metadatas.file_id LIKE CONCAT(publications.id_publication, "-%")');
            })
            ->get();

        if ($orphans->isNotEmpty()) {
            $count = $metadataService->syncMetadataForPublications($orphans);
            if ($count > 0) {
                // If we created new records, we should probably notify or log
                Log::info("MetadataReviewDashboard: Auto-generated {$count} missing metadata records.");
                session()->flash('notify', [
                    'message' => "Detected and fixed {$count} publications with missing metadata.",
                    'type' => 'info'
                ]);
            }
        }
    }

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
        $this->filterSections = $filters['sections'] ?? [];
        $this->filterAuthors = $filters['authors'] ?? [];
        $this->filterDateFrom = $filters['dateFrom'] ?? null;
        $this->filterDateTo = $filters['dateTo'] ?? null;
        $this->filterGenres = $filters['genres'] ?? [];
        $this->filterTextSizeRange = $filters['textSizeRange'] ?? [0, 500000];
        $this->filterAlphabeticalSort = $filters['alphabeticalSort'] ?? null;
        $this->filterPublicationStatus = $filters['publicationStatus'] ?? [];
        $this->statusFilter = $filters['statusFilter'] ?? 'all';
        $this->formatFilter = $filters['formatFilter'] ?? 'all';
        $this->dateFilter = $filters['dateFilter'] ?? 'all';

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
        $this->filterSections = [];
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
     * Stats now respect all active filters except statusFilter itself
     */
    private function getStats(): array
    {
        // Helper to build base query with all filters except status
        $getBaseQuery = function () {
            $query = FileMetadata::query();

            // Ensure we only count metadata for non-deleted publications
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('publications')
                    ->whereRaw('publications.id_publication = CAST(SUBSTRING_INDEX(file_metadatas.file_id, "-", 1) AS UNSIGNED)')
                    ->whereNull('deleted_at');
            });

            // Text search
            if ($this->search) {
                $searchTerm = trim($this->search);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('file_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('extracted_data->title', 'like', '%' . $searchTerm . '%');
                });
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

            // Apply publication filters
            if (!empty($this->filterSections)) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT DISTINCT p.id_publication
                        FROM publications p
                        JOIN section_publication sp ON p.id_publication = sp.publication_id
                        WHERE sp.section_id IN (?)
                    )
                ", [$this->filterSections]);
            }

            if (!empty($this->filterAuthors)) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT DISTINCT p.id_publication
                        FROM publications p
                        JOIN publication_authors pa ON p.id_publication = pa.publication_id
                        WHERE pa.author_id IN (?)
                    )
                ", [$this->filterAuthors]);
            }

            if ($this->filterDateFrom && $this->filterDateTo) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT id_publication FROM publications
                        WHERE upload_date BETWEEN ? AND ?
                    )
                ", [$this->filterDateFrom, $this->filterDateTo]);
            } elseif ($this->filterDateFrom) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT id_publication FROM publications
                        WHERE upload_date >= ?
                    )
                ", [$this->filterDateFrom]);
            } elseif ($this->filterDateTo) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT id_publication FROM publications
                        WHERE upload_date <= ?
                    )
                ", [$this->filterDateTo]);
            }

            if (!empty($this->filterGenres)) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT DISTINCT p.id_publication
                        FROM publications p
                        JOIN publication_genre pg ON p.id_publication = pg.publication_id
                        WHERE pg.genre_id IN (?)
                    )
                ", [$this->filterGenres]);
            }

            if ($this->filterTextSizeRange !== [0, 500000]) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT id_publication FROM publications
                        WHERE word_count BETWEEN ? AND ?
                    )
                ", [$this->filterTextSizeRange[0], $this->filterTextSizeRange[1]]);
            }

            if (!empty($this->filterPublicationStatus)) {
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                        SELECT id_publication FROM publications
                        WHERE status IN (?)
                    )
                ", [$this->filterPublicationStatus]);
            }

            return $query;
        };

        return [
            'pending' => (clone $getBaseQuery())->where('status', 'pending')->count(),
            'processed' => (clone $getBaseQuery())->where('status', 'processed')->count(),
            'confirmed' => (clone $getBaseQuery())->where('status', 'confirmed')->count(),
            'failed' => (clone $getBaseQuery())->where('status', 'failed')->count(),
            'rejected' => (clone $getBaseQuery())->where('status', 'rejected')->count(),
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
                $q->where('file_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('extracted_data->title', 'like', '%' . $searchTerm . '%');
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

        // Apply publication filters using direct publication_id from file_id
        // Note: file_id format is "publication_id-filename.ext", so we extract the ID

        // Helper to extract publication ID from file_id
        $extractPubId = fn($fileId) => (int) (explode('-', $fileId)[0] ?? 0);

        // Ensure we only show metadata for non-deleted publications
        $query->whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('publications')
                ->whereRaw('publications.id_publication = CAST(SUBSTRING_INDEX(file_metadatas.file_id, "-", 1) AS UNSIGNED)')
                ->whereNull('deleted_at');
        });

        // Publication section filter
        if (!empty($this->filterSections)) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT DISTINCT p.id_publication
                    FROM publications p
                    JOIN section_publication sp ON p.id_publication = sp.publication_id
                    WHERE sp.section_id IN (?)
                )
            ", [$this->filterSections]);
        }

        // Publication author filter
        if (!empty($this->filterAuthors)) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT DISTINCT p.id_publication
                    FROM publications p
                    JOIN publication_authors pa ON p.id_publication = pa.publication_id
                    WHERE pa.author_id IN (?)
                )
            ", [$this->filterAuthors]);
        }

        // Publication date range filter
        if ($this->filterDateFrom && $this->filterDateTo) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT id_publication FROM publications
                    WHERE upload_date BETWEEN ? AND ?
                )
            ", [$this->filterDateFrom, $this->filterDateTo]);
        } elseif ($this->filterDateFrom) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT id_publication FROM publications
                    WHERE upload_date >= ?
                )
            ", [$this->filterDateFrom]);
        } elseif ($this->filterDateTo) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT id_publication FROM publications
                    WHERE upload_date <= ?
                )
            ", [$this->filterDateTo]);
        }

        // Publication genre filter
        if (!empty($this->filterGenres)) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT DISTINCT p.id_publication
                    FROM publications p
                    JOIN publication_genre pg ON p.id_publication = pg.publication_id
                    WHERE pg.genre_id IN (?)
                )
            ", [$this->filterGenres]);
        }

        // Publication text size filter
        if ($this->filterTextSizeRange !== [0, 500000]) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT id_publication FROM publications
                    WHERE word_count BETWEEN ? AND ?
                )
            ", [$this->filterTextSizeRange[0], $this->filterTextSizeRange[1]]);
        }

        // Publication status filter
        if (!empty($this->filterPublicationStatus)) {
            $query->whereRaw("
                CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED) IN (
                    SELECT id_publication FROM publications
                    WHERE status IN (?)
                )
            ", [$this->filterPublicationStatus]);
        }

        // Apply sorting with qualified column names to avoid ambiguity
        $qualifiedColumn = in_array($this->sortBy, ['created_at', 'updated_at'])
            ? "file_metadatas.{$this->sortBy}"
            : $this->sortBy;
        $query->orderBy($qualifiedColumn, $this->sortDirection);

        // Note: publication() is not an Eloquent relationship due to composite file_id format,
        // so we don't use with('publication'). The blade template loads it on-demand.
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
     * Watch for selectAll changes and toggle items on current page
     */
    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            // Select all items on current page
            $this->selectedItems = $this->getFileMetadataList()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            // Deselect all items
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
                fn($item) => $item !== $idString
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
        $failed = 0;

        foreach ($this->selectedItems as $id) {
            $metadata = FileMetadata::find($id);
            if (!$metadata || !$metadata->file_id) {
                $failed++;

                continue;
            }

            // Extract publication ID from file_id format: "123-filename.pdf"
            $parts = explode('-', $metadata->file_id, 2);
            $publicationId = (int) ($parts[0] ?? 0);

            if ($publicationId === 0) {
                $failed++;

                continue;
            }

            // Get the file path from file_registration_logs (files table doesn't have file_path)
            $fileLog = DB::table('file_registration_logs')
                ->where('publication_id', $publicationId)
                ->where('file_path', 'like', '%' . addcslashes($metadata->file_name, '%_') . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$fileLog || !$fileLog->file_path) {
                Log::warning('File path not found for bulk re-extraction', [
                    'id' => $id,
                    'file_id' => $metadata->file_id,
                    'file_name' => $metadata->file_name,
                    'publication_id' => $publicationId,
                ]);
                $failed++;

                continue;
            }

            // Update status and queue for extraction
            $metadata->update(['status' => 'pending']);
            ExtractMetadataFromFile::dispatch($metadata->file_id, $fileLog->file_path, 1);
            $count++;
        }

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();

        $message = "{$count} items queued for re-extraction";
        if ($failed > 0) {
            $message .= " ({$failed} items failed)";
        }
        $this->dispatch('notify', message: $message, type: $failed > 0 ? 'warning' : 'success');
    }

    /**
     * Get count of confirmed items in current selection
     */
    public function getConfirmedCountInSelection(): int
    {
        if (empty($this->selectedItems)) {
            return 0;
        }

        return FileMetadata::whereIn('id', $this->selectedItems)
            ->where('status', 'confirmed')
            ->count();
    }

    /**
     * Extract metadata using Gemini AI for all selected items
     */
    public function extractWithAISelected(bool $forceOverwrite = false): void
    {
        Log::info('extractWithAISelected called', [
            'selected_count' => count($this->selectedItems),
            'force' => $forceOverwrite,
        ]);
        Log::channel('folder_scan')->info('extractWithAISelected called', [
            'selected_count' => count($this->selectedItems),
            'force' => $forceOverwrite,
        ]);
        if (!$this->geminiConfigured) {
            $this->dispatch('notify', message: __('Gemini API not configured'), type: 'error');

            return;
        }

        if (empty($this->selectedItems)) {
            $this->dispatch('notify', message: __('No items selected'), type: 'warning');

            return;
        }

        // Check for confirmed items and warn user if not forced
        $confirmedCount = $this->getConfirmedCountInSelection();
        if ($confirmedCount > 0 && !$forceOverwrite) {
            $this->dispatch('confirm-ai-extraction', confirmedCount: $confirmedCount);

            return;
        }

        $this->isExtractingWithAI = true;

        $success = 0;
        $failed = 0;
        $textExtractor = app(DocumentTextExtractor::class);
        $geminiService = app(GeminiMetadataExtractorService::class);
        $maxChars = config('services.gemini.max_chars', 5000);

        foreach ($this->selectedItems as $id) {
            try {
                $metadata = FileMetadata::find($id);
                if (!$metadata) {
                    $failed++;

                    continue;
                }

                // Extract publication ID from file_id format: "123-filename.pdf"
                $parts = explode('-', $metadata->file_id, 2);
                $publicationId = (int) ($parts[0] ?? 0);

                if ($publicationId === 0) {
                    Log::channel('folder_scan')->warning('Invalid publication ID in file_id', [
                        'id' => $id,
                        'file_id' => $metadata->file_id,
                    ]);
                    $failed++;

                    continue;
                }

                // Get file path from file_registration_logs (same pattern as reExtractSelected)
                $fileLog = DB::table('file_registration_logs')
                    ->where('publication_id', $publicationId)
                    ->where('file_path', 'like', '%' . addcslashes($metadata->file_name, '%_') . '%')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$fileLog || !$fileLog->file_path) {
                    Log::channel('folder_scan')->warning('File path not found for AI extraction', [
                        'id' => $id,
                        'file_id' => $metadata->file_id,
                        'file_name' => $metadata->file_name,
                        'publication_id' => $publicationId,
                    ]);
                    $failed++;

                    continue;
                }

                // Build file path from file_registration_logs
                $fullPath = $fileLog->file_path;

                // Handle relative paths versus absolute paths
                if (empty($fullPath)) {
                    $filePath = '';
                } elseif (preg_match('/^(\/|[A-Za-z]:)/', $fullPath)) {
                    // Already an absolute path (Unix or Windows)
                    $filePath = $fullPath;
                } else {
                    $filePath = storage_path('app/content/' . $fullPath);
                    if (!file_exists($filePath)) {
                        $filePath = storage_path('app/' . $fullPath);
                    }
                }

                if (!file_exists($filePath)) {
                    Log::channel('folder_scan')->warning('File not found for AI extraction', [
                        'id' => $id,
                        'file_id' => $metadata->file_id,
                        'path' => $filePath,
                    ]);
                    $failed++;

                    continue;
                }

                // Extract text
                try {
                    $text = $textExtractor->extractText($filePath, $maxChars);
                } catch (\RuntimeException $e) {
                    // User-friendly error (e.g., image-based PDF)
                    Log::channel('folder_scan')->warning('Text extraction blocked', [
                        'file_metadata_id' => $metadata->id,
                        'file_name' => $metadata->file_name,
                        'reason' => $e->getMessage(),
                    ]);
                    $failed++;

                    continue;
                }

                if (empty($text)) {
                    $failed++;

                    continue;
                }

                // Call Gemini API
                $extractedMetadata = $geminiService->extract($text);
                if ($extractedMetadata->isEmpty()) {
                    $failed++;

                    continue;
                }

                // Build extracted_data array
                $extractedData = [];

                if ($extractedMetadata->getTitle()) {
                    $extractedData['title'] = $extractedMetadata->getTitle();
                }

                $authors = $extractedMetadata->getAuthors();
                if (!empty($authors)) {
                    $extractedData['authors'] = $authors;
                }

                if ($extractedMetadata->getPublicationYear()) {
                    $extractedData['publication_year'] = $extractedMetadata->getPublicationYear();
                }

                if ($extractedMetadata->getPublisher()) {
                    $extractedData['publisher'] = $extractedMetadata->getPublisher();
                }

                $genres = $extractedMetadata->getGenres();
                if (!empty($genres)) {
                    $extractedData['genres'] = $genres;
                }

                $themes = $extractedMetadata->getThemes();
                if (!empty($themes)) {
                    $extractedData['themes'] = $themes;
                }

                if ($extractedMetadata->getContentType()) {
                    $extractedData['content_type'] = $extractedMetadata->getContentType();
                    // attempt to resolve ID if possible, but simpler to just store value and let form handle it
                }

                if ($extractedMetadata->getSection()) {
                    $extractedData['section'] = $extractedMetadata->getSection();
                }

                $extractedData['gemini_model'] = config('services.gemini.model', 'gemini-1.5-flash');

                // Update metadata
                $metadata->update([
                    'status' => 'processed',
                    'extraction_method' => 'gemini_llm',
                    'extracted_data' => $extractedData,
                ]);

                $success++;

                Log::channel('folder_scan')->info('Bulk Gemini extraction completed', [
                    'file_metadata_id' => $metadata->id,
                    'file_name' => $metadata->file_name,
                ]);

            } catch (\Exception $e) {
                Log::channel('folder_scan')->error('Bulk Gemini extraction failed', [
                    'id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->isExtractingWithAI = false;
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();

        $message = __(':success items extracted with AI', ['success' => $success]);
        if ($failed > 0) {
            $message .= ' (' . __(':failed failed', ['failed' => $failed]) . ')';
        }
        $this->dispatch('notify', message: $message, type: $failed > 0 ? 'warning' : 'success');
    }

    /**
     * Re-extract a single metadata record
     */
    public function reExtractSingle(int $id): void
    {
        $metadata = FileMetadata::find($id);
        if (!$metadata || !$metadata->file_id) {
            $this->dispatch('notify', message: 'Metadata not found', type: 'error');

            return;
        }

        // Extract publication ID from file_id format: "123-filename.pdf"
        $parts = explode('-', $metadata->file_id, 2);
        $publicationId = (int) ($parts[0] ?? 0);

        if ($publicationId === 0) {
            Log::warning('Invalid publication ID extracted from file_id', [
                'file_id' => $metadata->file_id,
            ]);
            $this->dispatch('notify', message: 'Invalid publication ID', type: 'error');

            return;
        }

        // Get the file path from file_registration_logs (files table doesn't have file_path)
        $fileLog = DB::table('file_registration_logs')
            ->where('publication_id', $publicationId)
            ->where('file_path', 'like', '%' . DB::raw('CONCAT(\'%-\', ?)') . '%', [$metadata->file_name])
            ->orWhere(function ($query) use ($publicationId, $metadata) {
                // Alternative: match by publication_id and filename pattern
                $query->where('publication_id', $publicationId)
                    ->where('file_path', 'like', '%' . addcslashes($metadata->file_name, '%_') . '%');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$fileLog || !$fileLog->file_path) {
            Log::warning('File path not found in file_registration_logs', [
                'publication_id' => $publicationId,
                'file_name' => $metadata->file_name,
                'file_id' => $metadata->file_id,
            ]);
            $this->dispatch('notify', message: 'File path not found', type: 'error');

            return;
        }

        // All validations passed - proceed with re-extraction
        $metadata->update(['status' => 'pending']);
        ExtractMetadataFromFile::dispatch($metadata->file_id, $fileLog->file_path, 1);
        $this->dispatch('notify', message: 'Re-extraction queued', type: 'success');
    }

    /**
     * Delete a metadata record and its associated publication
     */
    public function deleteMetadata(int $id): void
    {
        $metadata = FileMetadata::find($id);

        if ($metadata) {
            // Full cleanup of all related records
            if ($metadata->publication) {
                $pubId = $metadata->publication->id_publication;
                // Delete file registration logs
                FileRegistrationLog::where('publication_id', $pubId)->delete();
                // Delete file records
                File::where('id_publication', $pubId)->delete();
                // Delete the publication
                $metadata->publication->forceDelete();
            }

            $metadata->delete();
            $this->dispatch('notify', message: 'Publication deleted', type: 'success');
        }
    }

    /**
     * Delete all selected metadata records and their associated publications
     */
    public function deleteAllSelected(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', message: 'No items selected', type: 'warning');

            return;
        }

        $count = 0;

        foreach ($this->selectedItems as $id) {
            $metadata = FileMetadata::find($id);
            if ($metadata) {
                // Full cleanup of all related records
                if ($metadata->publication) {
                    $pubId = $metadata->publication->id_publication;
                    FileRegistrationLog::where('publication_id', $pubId)->delete();
                    File::where('id_publication', $pubId)->delete();
                    $metadata->publication->forceDelete();
                }
                $metadata->delete();
                $count++;
            }
        }

        $this->selectedItems = [];
        $this->selectAll = false;
        $this->resetPage();
        $this->dispatch('notify', message: "{$count} items deleted", type: 'success');
    }


    /**
     * Toggle publication status
     */
    public function togglePublicationStatus(int $publicationId, string $newStatus): void
    {
        if (!in_array($newStatus, ['published', 'hidden', 'pending'])) {
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
        if (empty($publicationIds) || !in_array($newStatus, ['published', 'hidden', 'pending'])) {
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

            $this->dispatch('notify', message: count($publicationIds) . " publications updated to {$newStatus}", type: 'success');
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

    /**
     * Get orphaned publications (publications without files)
     */
    public function getOrphanedPublications()
    {
        return Publication::whereDoesntHave('files')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get count of orphaned publications
     */
    public function getOrphanedCount(): int
    {
        return Publication::whereDoesntHave('files')->count();
    }

    /**
     * Toggle orphaned publications view
     */
    public function toggleOrphanedView(): void
    {
        $this->showOrphanedPublications = !$this->showOrphanedPublications;
    }

    /**
     * Delete an orphaned publication
     */
    public function deleteOrphanedPublication(int $id): void
    {
        $publication = Publication::find($id);
        if ($publication && $publication->files()->count() === 0) {
            $publication->forceDelete();
            $this->dispatch('notify', message: __('Orphaned publication deleted'), type: 'success');
        }
    }

    /**
     * Delete all orphaned publications
     */
    public function deleteAllOrphaned(): void
    {
        $count = Publication::whereDoesntHave('files')->count();
        Publication::whereDoesntHave('files')->forceDelete();
        $this->showOrphanedPublications = false;
        $this->dispatch('notify', message: __(':count orphaned publications deleted', ['count' => $count]), type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.metadata-review-dashboard', [
            'fileMetadataList' => $this->getFileMetadataList(),
            'stats' => $this->getStats(),
            'orphanedCount' => $this->getOrphanedCount(),
            'orphanedPublications' => $this->showOrphanedPublications ? $this->getOrphanedPublications() : collect(),
        ]);
    }
}
