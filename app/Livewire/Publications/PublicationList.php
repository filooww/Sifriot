<?php

declare(strict_types=1);

namespace App\Livewire\Publications;

use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PublicationList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $showDeleted = false;

    public $perPage = 15;

    public $isGuest = true;

    // Filter properties
    public array $filterSections = [];

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

    public function mount(): void
    {
        $this->isGuest = ! Auth::check();
    }

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

        $this->resetPage();
    }

    public function render()
    {
        // Check if MySQL (FULLTEXT) or SQLite (fallback to LIKE)
        $isMysql = DB::getDriverName() === 'mysql';

        // For guests, only show published publications
        $query = Publication::query()
            ->when($this->isGuest, function ($query) {
                // Guests see ONLY published publications
                $query->where('status', 'published');
            })
            ->when($this->search, function ($query) use ($isMysql) {
                if ($isMysql && ! empty(trim($this->search))) {
                    // Use FULLTEXT search on MySQL
                    $query->where(function ($q) {
                        $q->whereRaw(
                            'MATCH(title, title_low) AGAINST(? IN NATURAL LANGUAGE MODE)',
                            [trim($this->search)]
                        )
                            ->orWhereHas('authors', function ($q) {
                                $q->whereRaw(
                                    'MATCH(author, author_low) AGAINST(? IN NATURAL LANGUAGE MODE)',
                                    [trim($this->search)]
                                );
                            });
                    });
                } else {
                    // Fallback to LIKE search
                    $query->where(function ($q) {
                        $q->where('title', 'like', '%'.$this->search.'%')
                            ->orWhere('title_low', 'like', '%'.mb_strtolower($this->search).'%')
                            ->orWhereHas('authors', function ($q) {
                                $q->where('author', 'like', '%'.$this->search.'%')
                                    ->orWhere('author_low', 'like', '%'.mb_strtolower($this->search).'%');
                            });
                    });
                }
            })
            // Apply section filter
            ->when(! empty($this->filterSections), function ($query) {
                $query->whereHas('sections', fn ($q) => $q->whereIn('sections.id', $this->filterSections));
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
            ->when(! empty($this->filterPublicationStatus) && Auth::check() && Auth::user()->role === 'admin', function ($query) {
                $query->whereIn('status', $this->filterPublicationStatus);
            });

        // Only authenticated users can see deleted items
        if ($this->isGuest) {
            // Guests only see non-deleted publications
            $query->whereNull('deleted_at');
        } else {
            // Authenticated users can toggle deleted view
            if ($this->showDeleted) {
                $query->onlyTrashed(); // Only show soft-deleted
            }
            // If not showing deleted, default query shows only non-deleted
        }

        // For guests, eager load only basic relationships
        // For authenticated users, load all relationships including files
        if ($this->isGuest) {
            $query->with(['publishing', 'authorGroup', 'issueType', 'sections']);
        } else {
            $query->with(['publishing', 'authorGroup', 'themeSet', 'issueType', 'magazine', 'part', 'files', 'sections']);
        }

        // Apply alphabetical sort if set
        if ($this->filterAlphabeticalSort) {
            $direction = $this->filterAlphabeticalSort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('title', $direction);
        }
        // Order by relevance if searching with FULLTEXT, otherwise by date
        elseif ($isMysql && ! empty(trim($this->search))) {
            $query->orderByRaw('MATCH(title, title_low) AGAINST(? IN NATURAL LANGUAGE MODE) DESC', [trim($this->search)]);
        } else {
            $query->orderBy('upload_date', 'desc');
        }

        $publications = $query->paginate($this->perPage);

        // Calculate result count
        $resultCount = $publications->total();

        return view('livewire.publications.publication-list', [
            'publications' => $publications,
            'resultCount' => $resultCount,
        ]);
    }
}
