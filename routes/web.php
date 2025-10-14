<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Publications\PublicationList;

// Public/Guest accessible routes
Route::get('/', function () {
    return redirect()->route('publications.index');
});

Route::get('/publications', PublicationList::class)
    ->name('publications.index');

// Language switcher
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('language.switch');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');
});

require __DIR__.'/auth.php';
