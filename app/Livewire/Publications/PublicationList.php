<?php

namespace App\Livewire\Publications;

use App\Models\Publication;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PublicationList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $showDeleted = false;

    public $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleDeleted()
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    public function deletePublication($id)
    {
        $publication = Publication::find($id);
        if ($publication) {
            $publication->_del_mark = 1;
            $publication->save();
        }
    }

    public function restorePublication($id)
    {
        $publication = Publication::find($id);
        if ($publication) {
            $publication->_del_mark = 0;
            $publication->save();
        }
    }

    public function render()
    {
        $publications = Publication::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('title_low', 'like', '%' . mb_strtolower($this->search) . '%');
                });
            })
            ->when($this->showDeleted, function ($query) {
                $query->where('_del_mark', 1);
            }, function ($query) {
                $query->where('_del_mark', 0);
            })
            ->with(['publishing', 'authorGroup', 'themeSet', 'issueType', 'magazine', 'part'])
            ->orderBy('upload_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.publications.publication-list', [
            'publications' => $publications,
        ]);
    }
}
