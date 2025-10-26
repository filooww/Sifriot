<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\File;
use App\Models\FileRegistrationLog;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdminLibrarySettings extends Component
{
    public string $libraryPath = '';

    public function mount(): void
    {
        $this->libraryPath = config('library.storage.library_path');
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
        return view('livewire.admin.admin-library-settings', [
            'libraryPath' => $this->libraryPath,
        ])->layout('layouts.app');
    }
}
