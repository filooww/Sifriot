<?php

use App\Http\Controllers\DownloadController;
use App\Livewire\Publications\PublicationDetail;
use App\Livewire\Publications\PublicationList;
use Illuminate\Support\Facades\Route;

// Public/Guest accessible routes
Route::get('/', function () {
    return redirect()->route('publications.index');
});

Route::get('/publications', PublicationList::class)
    ->name('publications.index');

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
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');

    // File download (requires authentication)
    Route::get('/downloads/{publication}/{filename}', [DownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('role:user');
});

require __DIR__.'/auth.php';
