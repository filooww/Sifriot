<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\File;
use App\Models\FileRegistrationLog;
use App\Models\LibraryPath;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $path = trim($this->newPathInput);

        // Validate path exists
        if (! is_dir($path)) {
            session()->flash('error', __('Path not found or not accessible: ').$path.__('. Use paths within the Docker container (e.g., /library)'));

            return;
        }

        // Validate path is readable
        if (! is_readable($path)) {
            session()->flash('error', __('Insufficient permissions to read path: ').$path);

            return;
        }

        // Check for duplicates
        if (LibraryPath::where('path', $path)->exists()) {
            session()->flash('error', __('This path is already configured'));

            return;
        }

        LibraryPath::create([
            'path' => $path,
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

    public function cleanupAllPublications(): void
    {
        try {
            DB::transaction(function () {
                // Disable foreign key checks to allow truncation
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                try {
                    // Delete all file registration logs
                    FileRegistrationLog::truncate();

                    // Delete all files
                    File::truncate();

                    // Delete all publications (and related pivot tables)
                    DB::table('author_publication')->truncate();
                    Publication::truncate();

                    Log::channel('admin')->warning('All publications deleted by user', [
                        'user_id' => auth()->id(),
                        'user_name' => auth()->user()->name ?? 'Unknown',
                    ]);
                } finally {
                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            });

            session()->flash('success', __('All publications have been deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Failed to cleanup publications', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', __('Failed to cleanup publications: ').$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-library-settings')
            ->layout('layouts.app');
    }
}
