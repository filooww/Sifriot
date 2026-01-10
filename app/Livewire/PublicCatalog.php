<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PublicCatalog extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public $perPage = 15;

    public $isGuest = true;

    // Filter properties
    public array $filterContentTypes = [];

    public array $filterAuthors = [];

    public ?string $filterDateFrom = null;

    public ?string $filterDateTo = null;

    public array $filterGenres = [];

    public array $filterTextSizeRange = [0, 500000];

    public ?string $filterAlphabeticalSort = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->isGuest = ! Auth::check();
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
        $this->filterAuthors = $filters['authors'] ?? [];
        $this->filterDateFrom = $filters['dateFrom'] ?? null;
        $this->filterDateTo = $filters['dateTo'] ?? null;
        $this->filterGenres = $filters['genres'] ?? [];
        $this->filterTextSizeRange = $filters['textSizeRange'] ?? [0, 500000];
        $this->filterAlphabeticalSort = $filters['alphabeticalSort'] ?? null;

        $this->resetPage();
    }

    public function render()
    {
        // Public catalog only shows active, non-deleted publications
        $query = Publication::query()
            ->when($this->isGuest || (Auth::check() && Auth::user()->role !== 'admin'), function ($query) {
                // Guests and non-admin users can only see published publications
                $query->where('status', 'published');
            })
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
            });

        // Public catalog: only show non-deleted publications
        $query->whereNull('deleted_at');

        // Eager load basic relationships (including files for cover image display)
        $query->with(['publishing', 'authorGroup', 'issueType', 'contentType', 'authors', 'files']);

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

        // Calculate result count
        $resultCount = $publications->total();

        return view('livewire.public-catalog', [
            'publications' => $publications,
            'resultCount' => $resultCount,
        ]);
    }
}
