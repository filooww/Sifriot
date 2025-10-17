<?php

namespace App\Livewire\Publications;

use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
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

    public function render()
    {
        // Check if MySQL (FULLTEXT) or SQLite (fallback to LIKE)
        $isMysql = DB::getDriverName() === 'mysql';

        // For guests, only show active publications
        $query = Publication::query()
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
            $query->with(['publishing', 'authorGroup', 'issueType']);
        } else {
            $query->with(['publishing', 'authorGroup', 'themeSet', 'issueType', 'magazine', 'part', 'files']);
        }

        // Order by relevance if searching with FULLTEXT, otherwise by date
        if ($isMysql && ! empty(trim($this->search))) {
            $publications = $query
                ->orderByRaw('MATCH(title, title_low) AGAINST(? IN NATURAL LANGUAGE MODE) DESC', [trim($this->search)])
                ->paginate($this->perPage);
        } else {
            $publications = $query->orderBy('upload_date', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.publications.publication-list', [
            'publications' => $publications,
        ]);
    }
}
