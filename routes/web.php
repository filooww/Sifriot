<?php

use App\Http\Controllers\DownloadController;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\AdminLibrarySettings;
use App\Livewire\Admin\BulkFolderScanner;
use App\Livewire\Admin\FileRegistrationForm;
use App\Livewire\Admin\FolderBrowser;
use App\Livewire\Admin\ScanResultsViewer;
use App\Livewire\Publications\PublicationDetail;
use App\Livewire\PublicCatalog;
use App\Livewire\User\UserProfile;
use Illuminate\Support\Facades\Route;

// Public/Guest accessible routes
Route::get('/', PublicCatalog::class)
    ->name('home');

Route::get('/publications/{id}', PublicationDetail::class)
    ->name('publications.show');

// Language switcher
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru', 'he'])) {
        session(['locale' => $locale]);

        // Update authenticated user's preference
        if (auth()->check()) {
            auth()->user()->update(['preferred_language' => $locale]);
        }
    }

    return redirect()->back();
})->name('language.switch');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', UserProfile::class)
        ->name('profile');

    // File download (requires authentication)
    Route::get('/downloads/{publication}/{filename}', [DownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('role:user');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', AdminDashboard::class)
        ->name('dashboard');

    Route::get('/admin/files/browse', FolderBrowser::class)
        ->name('admin.files.browse');

    Route::get('/admin/files/register', FileRegistrationForm::class)
        ->name('admin.files.register');

    Route::get('/admin/bulk-scan', BulkFolderScanner::class)
        ->name('admin.bulk-scan');

    Route::get('/admin/scan-results/{scanJobId}', ScanResultsViewer::class)
        ->name('admin.scan-results');

    Route::get('/admin/settings/library-paths', AdminLibrarySettings::class)
        ->name('admin.settings.library-paths');
});

require __DIR__.'/auth.php';
