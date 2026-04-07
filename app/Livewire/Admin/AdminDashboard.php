<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $showDeleted = false;

    public $perPage = 15;

    // Filter properties
    public array $filterContentTypes = [];

    public array $filterSections = [];

    public array $filterPublishers = [];

    public array $filterAuthors = [];

    public ?string $filterDateFrom = null;

    public ?string $filterDateTo = null;

    public array $filterGenres = [];

    public array $filterTextSizeRange = [0, 500000];

    public ?string $filterAlphabeticalSort = null;

    public array $filterPublicationStatus = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleDeleted()
    {
        $this->showDeleted = ! $this->showDeleted;
        $this->resetPage();
    }

    public function deletePublication($id)
    {
        $publication = Publication::find($id);
        if ($publication) {
            $publication->delete(); // Soft delete
        }
    }

    public function restorePublication($id)
    {
        $publication = Publication::withTrashed()->find($id);
        if ($publication) {
            $publication->restore(); // Restore soft-deleted
        }
    }

    #[On('searchUpdated')]
    public function updateSearch(string $searchQuery): void
    {
        $this->search = $searchQuery;
        $this->resetPage();
    }

    #[On('filtersChanged')]
    public function applyFilters(array $filters): void
    {
        $this->filterContentTypes = $filters['contentTypes'] ?? [];
        $this->filterSections = $filters['sections'] ?? [];
        $this->filterPublishers = $filters['publishers'] ?? [];
        $this->filterAuthors = $filters['authors'] ?? [];
        $this->filterDateFrom = $filters['dateFrom'] ?? null;
        $this->filterDateTo = $filters['dateTo'] ?? null;
        $this->filterGenres = $filters['genres'] ?? [];
        $this->filterTextSizeRange = $filters['textSizeRange'] ?? [0, 500000];
        $this->filterAlphabeticalSort = $filters['alphabeticalSort'] ?? null;
        $this->filterPublicationStatus = $filters['publicationStatus'] ?? [];

        $this->resetPage();
    }

    public function render()
    {
        // Check if MySQL (FULLTEXT) or SQLite (fallback to LIKE)
        $isMysql = DB::getDriverName() === 'mysql';

        // Admin dashboard shows all publications (including deleted if toggled)
        $query = Publication::query()
            ->when($this->search, function ($query) {
                $searchTerm = trim($this->search);
                if (! empty($searchTerm)) {
                    // Use LIKE for partial matching (works for all databases)
                    // This ensures "Dolore" matches "Dolores" and any partial input
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('title', 'like', '%'.$searchTerm.'%')
                            ->orWhere('title_low', 'like', '%'.mb_strtolower($searchTerm).'%')
                            ->orWhereHas('authors', function ($q) use ($searchTerm) {
                                $q->where('author', 'like', '%'.$searchTerm.'%')
                                    ->orWhere('author_low', 'like', '%'.mb_strtolower($searchTerm).'%');
                            });
                    });
                }
            })
            // Apply content type filter
            ->when(! empty($this->filterContentTypes), function ($query) {
                $query->whereIn('content_type_id', $this->filterContentTypes);
            })
            // Apply section filter
            ->when(! empty($this->filterSections), function ($query) {
                $query->whereHas('sections', fn ($q) => $q->whereIn('sections.id', $this->filterSections));
            })
            // Apply publisher filter
            ->when(! empty($this->filterPublishers), function ($query) {
                $query->whereHas('publishers', fn ($q) => $q->whereIn('publishers.id', $this->filterPublishers));
            })
            // Apply author filter
            ->when(! empty($this->filterAuthors), function ($query) {
                $query->whereHas('authors', fn ($q) => $q->whereIn('authors.id_author', $this->filterAuthors));
            })
            // Apply date range filter
            ->when($this->filterDateFrom && $this->filterDateTo, function ($query) {
                $query->whereBetween('upload_date', [$this->filterDateFrom, $this->filterDateTo]);
            })
            ->when($this->filterDateFrom && ! $this->filterDateTo, function ($query) {
                $query->where('upload_date', '>=', $this->filterDateFrom);
            })
            ->when(! $this->filterDateFrom && $this->filterDateTo, function ($query) {
                $query->where('upload_date', '<=', $this->filterDateTo);
            })
            // Apply genre filter
            ->when(! empty($this->filterGenres), function ($query) {
                $query->whereHas('themes', fn ($q) => $q->whereIn('themes.id_theme', $this->filterGenres));
            })
            // Apply text size filter
            ->when($this->filterTextSizeRange !== [0, 500000], function ($query) {
                $query->whereBetween('word_count', [$this->filterTextSizeRange[0], $this->filterTextSizeRange[1]]);
            })
            // Apply publication status filter (admin only)
            ->when(! empty($this->filterPublicationStatus), function ($query) {
                $query->whereIn('status', $this->filterPublicationStatus);
            });

        // Handle soft deletes
        if ($this->showDeleted) {
            $query->onlyTrashed(); // Only show soft-deleted
        }

        // Eager load all relationships for admin
        $query->with(['issueType', 'magazine', 'part', 'files', 'sections', 'authors', 'publishers', 'contentType']);

        // Apply alphabetical sort if set
        if ($this->filterAlphabeticalSort) {
            $direction = $this->filterAlphabeticalSort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('title', $direction);
        }
        // When searching, prioritize exact matches at the beginning
        elseif (! empty(trim($this->search))) {
            $searchTerm = trim($this->search);
            // Order by: exact title match first, then prefix match, then contains match, then by date
            $query->orderByRaw('
                CASE
                    WHEN title = ? THEN 1
                    WHEN title LIKE ? THEN 2
                    WHEN title LIKE ? THEN 3
                    ELSE 4
                END
            ', [$searchTerm, $searchTerm.'%', '%'.$searchTerm.'%'])
                ->orderBy('upload_date', 'desc');
        } else {
            $query->orderBy('upload_date', 'desc');
        }

        $publications = $query->paginate($this->perPage);

        // Calculate statistics
        $totalPublications = Publication::count();
        $pendingCount = Publication::where('status', 'pending')->count();
        $recentUploads = Publication::where('upload_date', '>=', now()->subDays(7))->count();

        // Calculate result count
        $resultCount = $publications->total();

        return view('livewire.admin.admin-dashboard', [
            'publications' => $publications,
            'resultCount' => $resultCount,
            'totalPublications' => $totalPublications,
            'pendingCount' => $pendingCount,
            'recentUploads' => $recentUploads,
        ]);
    }
}
