<?php

declare(strict_types=1);

namespace App\Livewire\Publications;

use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PublicationDetail extends Component
{
    public Publication $publication;

    public bool $isGuest = true;

    public function mount(int $id): void
    {
        $this->isGuest = ! Auth::check();

        // Load publication with relationships
        $this->publication = Publication::query()
            ->with([
                'publishing',
                'authorGroup',
                'issueType',
                'magazine',
                'themeSet',
                'part',
                'authors',
                'themes',
            ])
            ->when(! $this->isGuest, function ($query) {
                $query->with('files');
            })
            ->whereNull('deleted_at')
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.publications.publication-detail');
    }
}
