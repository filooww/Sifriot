<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\LibraryPath;
use Livewire\Component;

class AdminLibrarySettings extends Component
{
    public $libraryPaths = [];

    public string $newPathInput = '';

    public bool $showFolderPicker = false;

    public function mount(): void
    {
        $this->loadPaths();
    }

    public function loadPaths(): void
    {
        $this->libraryPaths = LibraryPath::with('creator')->orderBy('created_at', 'desc')->get();
    }

    public function addPath(): void
    {
        $this->validate([
            'newPathInput' => 'required|string|max:500',
        ]);

        // Validate path exists and is readable
        if (! is_dir($this->newPathInput) || ! is_readable($this->newPathInput)) {
            session()->flash('error', __('Invalid path or insufficient permissions'));

            return;
        }

        // Check for duplicates
        if (LibraryPath::where('path', $this->newPathInput)->exists()) {
            session()->flash('error', __('This path is already configured'));

            return;
        }

        LibraryPath::create([
            'path' => $this->newPathInput,
            'is_active' => true,
            'last_verified_at' => now(),
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', __('Path added successfully'));
        $this->newPathInput = '';
        $this->loadPaths();
    }

    public function removePath(int $pathId): void
    {
        $path = LibraryPath::findOrFail($pathId);
        $path->delete();

        session()->flash('success', __('Path removed'));
        $this->loadPaths();
    }

    public function verifyPath(int $pathId): void
    {
        $path = LibraryPath::findOrFail($pathId);

        // Check path still exists and readable
        if (is_dir($path->path) && is_readable($path->path)) {
            $path->update([
                'is_active' => true,
                'last_verified_at' => now(),
            ]);
            session()->flash('success', __('Path verified successfully'));
        } else {
            $path->update([
                'is_active' => false,
                'last_verified_at' => now(),
            ]);
            session()->flash('error', __('Path verification failed'));
        }

        $this->loadPaths();
    }

    public function openFolderPicker(): void
    {
        $this->showFolderPicker = true;
    }

    public function closeFolderPicker(): void
    {
        $this->showFolderPicker = false;
    }

    public function selectPath(string $path): void
    {
        $this->newPathInput = $path;
        $this->closeFolderPicker();
    }

    public function render()
    {
        return view('livewire.admin.admin-library-settings')
            ->layout('layouts.app');
    }
}
