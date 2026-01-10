<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FileViewController;
use App\Livewire\Admin\AdminLibrarySettings;
use App\Livewire\Admin\BulkFolderScanner;
use App\Livewire\Admin\FileManagement;
use App\Livewire\Admin\FileRegistrationForm;
use App\Livewire\Admin\FolderBrowser;
use App\Livewire\Admin\MetadataReviewDashboard;
use App\Livewire\Admin\ScanResultsViewer;
use App\Livewire\Publications\PublicationDetail;
use App\Livewire\Publications\PublicationPreview;
use App\Livewire\PublicCatalog;
use App\Livewire\User\UserProfile;
use Illuminate\Support\Facades\Route;

// Public/Guest accessible routes
Route::get('/', PublicCatalog::class)
    ->name('home');

Route::get('/publications/{id}', PublicationDetail::class)
    ->name('publications.show');

Route::get('/publications/{id}/preview', PublicationPreview::class)
    ->name('publications.preview');

// Public cover image access (no auth required for previews)
Route::get('/covers/{publication}/{filename}', [FileViewController::class, 'serveCover'])
    ->name('covers.serve');

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

    // File viewer (inline viewing for document viewers)
    // Filename is base64-encoded to handle Cyrillic characters
    Route::get('/files/view/{publication}/{filename}', [FileViewController::class, 'view'])
        ->name('files.view');

    // DOC file converter (converts DOC to text using antiword)
    Route::get('/files/convert-doc/{publication}/{filename}', [FileViewController::class, 'convertDoc'])
        ->name('files.convert-doc');

    // DOC file converter to HTML (converts DOC to styled HTML using PHPWord)
    Route::get('/files/convert-doc-html/{publication}/{filename}', [FileViewController::class, 'convertDocToHtml'])
        ->name('files.convert-doc-html');

    // FB2 file converter (converts FB2 XML to HTML)
    Route::get('/files/convert-fb2/{publication}/{filename}', [FileViewController::class, 'convertFb2'])
        ->name('files.convert-fb2');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', MetadataReviewDashboard::class)
        ->name('dashboard');

    // Unified file management page
    Route::get('/admin/files', FileManagement::class)
        ->name('admin.files');

    // Legacy routes for backwards compatibility
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

    // Content Types and Custom Fields management
    Route::get('/admin/content-types', \App\Livewire\Admin\ContentTypeManager::class)
        ->name('admin.content-types');

    Route::get('/admin/content-types/{contentTypeId}/fields', \App\Livewire\Admin\CustomFieldManager::class)
        ->name('admin.content-types.fields');
});

require __DIR__.'/auth.php';
