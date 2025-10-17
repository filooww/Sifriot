<?php

declare(strict_types=1);

namespace App\Livewire\Search;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $searchQuery = '';

    public bool $showResults = false;

    public int $maxResults = 10;

    /**
     * Perform FULLTEXT search on publications and authors.
     */
    public function search(): Collection
    {
        if (empty(trim($this->searchQuery))) {
            return new Collection;
        }

        $sanitizedQuery = $this->sanitizeSearchQuery($this->searchQuery);

        // Check if MySQL (FULLTEXT) or SQLite (fallback to LIKE)
        $isMysql = DB::getDriverName() === 'mysql';

        if ($isMysql) {
            return $this->fulltextSearch($sanitizedQuery);
        }

        return $this->likeSearch($sanitizedQuery);
    }

    /**
     * FULLTEXT search using MySQL MATCH AGAINST.
     */
    private function fulltextSearch(string $query): Collection
    {
        $publications = Publication::query()
            ->where(function ($q) use ($query) {
                // Search by publication title
                $q->whereRaw(
                    'MATCH(title, title_low) AGAINST(? IN NATURAL LANGUAGE MODE)',
                    [$query]
                );
            })
            ->orWhereHas('authors', function ($q) use ($query) {
                // Search by author name
                $q->whereRaw(
                    'MATCH(author, author_low) AGAINST(? IN NATURAL LANGUAGE MODE)',
                    [$query]
                );
            })
            ->whereNull('deleted_at');

        // Eager load relationships based on user authentication
        if (Auth::check()) {
            $publications->with(['publishing', 'authorGroup', 'issueType', 'files', 'themes']);
        } else {
            $publications->with(['publishing', 'authorGroup', 'issueType']);
        }

        return $publications
            ->orderByRaw('MATCH(title, title_low) AGAINST(? IN NATURAL LANGUAGE MODE) DESC', [$query])
            ->limit($this->maxResults)
            ->get();
    }

    /**
     * Fallback LIKE search for SQLite or when FULLTEXT is not available.
     */
    private function likeSearch(string $query): Collection
    {
        $publications = Publication::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%'.$query.'%')
                    ->orWhere('title_low', 'like', '%'.mb_strtolower($query).'%');
            })
            ->orWhereHas('authors', function ($q) use ($query) {
                $q->where('author', 'like', '%'.$query.'%')
                    ->orWhere('author_low', 'like', '%'.mb_strtolower($query).'%');
            })
            ->whereNull('deleted_at');

        // Eager load relationships based on user authentication
        if (Auth::check()) {
            $publications->with(['publishing', 'authorGroup', 'issueType', 'files', 'themes']);
        } else {
            $publications->with(['publishing', 'authorGroup', 'issueType']);
        }

        return $publications
            ->limit($this->maxResults)
            ->get();
    }

    /**
     * Sanitize search query to prevent SQL injection.
     */
    private function sanitizeSearchQuery(string $query): string
    {
        return trim($query);
    }

    /**
     * Highlight search terms in text.
     */
    public function highlightSearchTerms(string $text, string $searchQuery): string
    {
        if (empty($searchQuery)) {
            return htmlspecialchars($text);
        }

        // Escape HTML first
        $escapedText = htmlspecialchars($text);

        // Build regex pattern - case insensitive with Unicode support
        $pattern = '/('.preg_quote($searchQuery, '/').')/iu';

        // Wrap matches in <mark> tags
        return preg_replace(
            $pattern,
            '<mark class="bg-yellow-200 dark:bg-yellow-800 font-semibold">$1</mark>',
            $escapedText
        );
    }

    /**
     * Update search results visibility.
     */
    public function updatedSearchQuery(): void
    {
        $this->showResults = ! empty(trim($this->searchQuery));
    }

    /**
     * Clear search query.
     */
    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->showResults = false;
    }

    public function render()
    {
        $results = $this->search();

        return view('livewire.search.global-search', [
            'results' => $results,
            'hasResults' => $results->isNotEmpty(),
        ]);
    }
}
