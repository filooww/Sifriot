<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Author;
use App\Models\Category;
use App\Models\ContentType;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Theme;
use Livewire\Attributes\Url;
use Livewire\Component;

class FiltrationManagement extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'content-types';

    // Content Types
    public $contentTypes = [];

    public bool $showContentTypeModal = false;

    public ?ContentType $editingContentType = null;

    public string $ct_name_en = '';

    public string $ct_name_ru = '';

    public string $ct_name_he = '';

    public string $ct_slug = '';

    public string $ct_icon = '';

    public string $ct_folder_name = '';

    public bool $ct_is_system = false;

    // Genres
    public $genres = [];

    public bool $showGenreModal = false;

    public ?Genre $editingGenre = null;

    public string $genre_name_en = '';

    public string $genre_name_ru = '';

    public string $genre_name_he = '';

    public string $genre_slug = '';

    // Themes
    public $themes = [];

    public bool $showThemeModal = false;

    public ?Theme $editingTheme = null;

    public string $theme_name = '';

    public string $theme_name_low = '';

    // Categories
    public $categories = [];

    public $parentCategories = [];

    public bool $showCategoryModal = false;

    public ?Category $editingCategory = null;

    public string $category_name_en = '';

    public string $category_name_ru = '';

    public string $category_name_he = '';

    public string $category_slug = '';

    public ?int $category_parent_id = null;

    public int $category_sort_order = 0;

    // Authors
    public $authors = [];

    public bool $showAuthorModal = false;

    public ?Author $editingAuthor = null;

    public string $author_name = '';

    public string $author_name_low = '';

    // Publishers
    public $publishers = [];

    public bool $showPublisherModal = false;

    public ?Publisher $editingPublisher = null;

    public string $publisher_name_en = '';

    public string $publisher_name_ru = '';

    public string $publisher_name_he = '';

    public string $publisher_slug = '';

    public string $publisher_website = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');
        $this->loadDataForActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->loadDataForActiveTab();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->loadDataForActiveTab();
    }

    private function loadDataForActiveTab(): void
    {
        match ($this->activeTab) {
            'content-types' => $this->loadContentTypes(),
            'genres' => $this->loadGenres(),
            'themes' => $this->loadThemes(),
            'categories' => $this->loadCategories(),
            'authors' => $this->loadAuthors(),
            'publishers' => $this->loadPublishers(),
            default => $this->loadContentTypes(),
        };
    }

    // ==================== CONTENT TYPES ====================

    public function loadContentTypes(): void
    {
        $this->contentTypes = ContentType::withCount('publications')->get();
    }

    public function createContentType(): void
    {
        $this->resetContentTypeForm();
        $this->showContentTypeModal = true;
    }

    public function editContentType(int $id): void
    {
        $this->editingContentType = ContentType::findOrFail($id);
        $this->ct_name_en = $this->editingContentType->name_en;
        $this->ct_name_ru = $this->editingContentType->name_ru ?? '';
        $this->ct_name_he = $this->editingContentType->name_he ?? '';
        $this->ct_slug = $this->editingContentType->slug;
        $this->ct_icon = $this->editingContentType->icon ?? '';
        $this->ct_folder_name = $this->editingContentType->folder_name ?? '';
        $this->ct_is_system = $this->editingContentType->is_system;
        $this->showContentTypeModal = true;
    }

    public function saveContentType(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $contentTypeId = $this->editingContentType?->id;
        $validated = $this->validate([
            'ct_name_en' => 'required|string|max:255',
            'ct_name_ru' => 'nullable|string|max:255',
            'ct_name_he' => 'nullable|string|max:255',
            'ct_slug' => "required|string|max:255|unique:content_types,slug,{$contentTypeId}",
            'ct_icon' => 'nullable|string|max:255',
            'ct_folder_name' => 'nullable|string|max:255',
            'ct_is_system' => 'boolean',
        ]);

        $data = [
            'name_en' => $validated['ct_name_en'],
            'name_ru' => $validated['ct_name_ru'],
            'name_he' => $validated['ct_name_he'],
            'slug' => $validated['ct_slug'],
            'icon' => $validated['ct_icon'],
            'folder_name' => $validated['ct_folder_name'],
            'is_system' => $validated['ct_is_system'],
        ];

        if ($this->editingContentType) {
            $this->editingContentType->update($data);
            session()->flash('message', __('Content type updated successfully.'));
        } else {
            ContentType::create($data);
            session()->flash('message', __('Content type created successfully.'));
        }

        $this->showContentTypeModal = false;
        $this->resetContentTypeForm();
        $this->loadContentTypes();
    }

    public function deleteContentType(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $contentType = ContentType::withCount('publications')->findOrFail($id);

        if ($contentType->is_system) {
            session()->flash('error', __('Cannot delete system content types.'));

            return;
        }

        if ($contentType->publications_count > 0) {
            session()->flash('error', __('Cannot delete content type with associated publications.'));

            return;
        }

        $contentType->delete();
        session()->flash('message', __('Content type deleted successfully.'));
        $this->loadContentTypes();
    }

    private function resetContentTypeForm(): void
    {
        $this->editingContentType = null;
        $this->ct_name_en = '';
        $this->ct_name_ru = '';
        $this->ct_name_he = '';
        $this->ct_slug = '';
        $this->ct_icon = '';
        $this->ct_folder_name = '';
        $this->ct_is_system = false;
    }

    // ==================== GENRES ====================

    public function loadGenres(): void
    {
        $this->genres = Genre::withCount('publications')->get();
    }

    public function createGenre(): void
    {
        $this->resetGenreForm();
        $this->showGenreModal = true;
    }

    public function editGenre(int $id): void
    {
        $this->editingGenre = Genre::findOrFail($id);
        $this->genre_name_en = $this->editingGenre->name_en;
        $this->genre_name_ru = $this->editingGenre->name_ru ?? '';
        $this->genre_name_he = $this->editingGenre->name_he ?? '';
        $this->genre_slug = $this->editingGenre->slug;
        $this->showGenreModal = true;
    }

    public function saveGenre(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $genreId = $this->editingGenre?->id;
        $validated = $this->validate([
            'genre_name_en' => 'required|string|max:255',
            'genre_name_ru' => 'nullable|string|max:255',
            'genre_name_he' => 'nullable|string|max:255',
            'genre_slug' => "required|string|max:255|unique:genres,slug,{$genreId}",
        ]);

        $data = [
            'name_en' => $validated['genre_name_en'],
            'name_ru' => $validated['genre_name_ru'],
            'name_he' => $validated['genre_name_he'],
            'slug' => $validated['genre_slug'],
        ];

        if ($this->editingGenre) {
            $this->editingGenre->update($data);
            session()->flash('message', __('Genre updated successfully.'));
        } else {
            Genre::create($data);
            session()->flash('message', __('Genre created successfully.'));
        }

        $this->showGenreModal = false;
        $this->resetGenreForm();
        $this->loadGenres();
    }

    public function deleteGenre(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $genre = Genre::withCount('publications')->findOrFail($id);

        if ($genre->publications_count > 0) {
            session()->flash('error', __('Cannot delete genre with associated publications.'));

            return;
        }

        $genre->delete();
        session()->flash('message', __('Genre deleted successfully.'));
        $this->loadGenres();
    }

    private function resetGenreForm(): void
    {
        $this->editingGenre = null;
        $this->genre_name_en = '';
        $this->genre_name_ru = '';
        $this->genre_name_he = '';
        $this->genre_slug = '';
    }

    // ==================== THEMES ====================

    public function loadThemes(): void
    {
        $this->themes = Theme::withCount('publications')->get();
    }

    public function createTheme(): void
    {
        $this->resetThemeForm();
        $this->showThemeModal = true;
    }

    public function editTheme(int $id): void
    {
        $this->editingTheme = Theme::findOrFail($id);
        $this->theme_name = $this->editingTheme->theme;
        $this->theme_name_low = $this->editingTheme->theme_low ?? '';
        $this->showThemeModal = true;
    }

    public function saveTheme(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $this->validate([
            'theme_name' => 'required|string|max:255',
            'theme_name_low' => 'nullable|string|max:255',
        ]);

        $data = [
            'theme' => $validated['theme_name'],
            'theme_low' => mb_strtolower($validated['theme_name']),
        ];

        if ($this->editingTheme) {
            $this->editingTheme->update($data);
            session()->flash('message', __('Theme updated successfully.'));
        } else {
            Theme::create($data);
            session()->flash('message', __('Theme created successfully.'));
        }

        $this->showThemeModal = false;
        $this->resetThemeForm();
        $this->loadThemes();
    }

    public function deleteTheme(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $theme = Theme::withCount('publications')->findOrFail($id);

        if ($theme->publications_count > 0) {
            session()->flash('error', __('Cannot delete theme with associated publications.'));

            return;
        }

        $theme->delete();
        session()->flash('message', __('Theme deleted successfully.'));
        $this->loadThemes();
    }

    private function resetThemeForm(): void
    {
        $this->editingTheme = null;
        $this->theme_name = '';
        $this->theme_name_low = '';
    }

    // ==================== CATEGORIES ====================

    public function loadCategories(): void
    {
        $this->categories = Category::with('parent')
            ->withCount(['children', 'publications'])
            ->orderBy('sort_order')
            ->get();
    }

    public function createCategory(): void
    {
        $this->resetCategoryForm();
        $this->parentCategories = Category::orderBy('name_en')->get();
        $this->showCategoryModal = true;
    }

    public function editCategory(int $id): void
    {
        $this->editingCategory = Category::findOrFail($id);
        $this->category_name_en = $this->editingCategory->name_en;
        $this->category_name_ru = $this->editingCategory->name_ru ?? '';
        $this->category_name_he = $this->editingCategory->name_he ?? '';
        $this->category_slug = $this->editingCategory->slug;
        $this->category_parent_id = $this->editingCategory->parent_id;
        $this->category_sort_order = $this->editingCategory->sort_order ?? 0;
        $this->parentCategories = Category::where('id', '!=', $id)->orderBy('name_en')->get();
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $categoryId = $this->editingCategory?->id;
        $validated = $this->validate([
            'category_name_en' => 'required|string|max:255',
            'category_name_ru' => 'nullable|string|max:255',
            'category_name_he' => 'nullable|string|max:255',
            'category_slug' => "required|string|max:255|unique:categories,slug,{$categoryId}",
            'category_parent_id' => 'nullable|exists:categories,id',
            'category_sort_order' => 'integer|min:0',
        ]);

        $data = [
            'name_en' => $validated['category_name_en'],
            'name_ru' => $validated['category_name_ru'],
            'name_he' => $validated['category_name_he'],
            'slug' => $validated['category_slug'],
            'parent_id' => $validated['category_parent_id'],
            'sort_order' => $validated['category_sort_order'],
        ];

        if ($this->editingCategory) {
            $this->editingCategory->update($data);
            session()->flash('message', __('Category updated successfully.'));
        } else {
            Category::create($data);
            session()->flash('message', __('Category created successfully.'));
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        $this->loadCategories();
    }

    public function deleteCategory(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $category = Category::withCount(['children', 'publications'])->findOrFail($id);

        if ($category->children_count > 0) {
            session()->flash('error', __('Cannot delete category with child categories.'));

            return;
        }

        if ($category->publications_count > 0) {
            session()->flash('error', __('Cannot delete category with associated publications.'));

            return;
        }

        $category->delete();
        session()->flash('message', __('Category deleted successfully.'));
        $this->loadCategories();
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategory = null;
        $this->category_name_en = '';
        $this->category_name_ru = '';
        $this->category_name_he = '';
        $this->category_slug = '';
        $this->category_parent_id = null;
        $this->category_sort_order = 0;
        $this->parentCategories = [];
    }

    // ==================== AUTHORS ====================

    public function loadAuthors(): void
    {
        $this->authors = Author::withCount('publications')->get();
    }

    public function createAuthor(): void
    {
        $this->resetAuthorForm();
        $this->showAuthorModal = true;
    }

    public function editAuthor(int $id): void
    {
        $this->editingAuthor = Author::findOrFail($id);
        $this->author_name = $this->editingAuthor->author;
        $this->author_name_low = $this->editingAuthor->author_low ?? '';
        $this->showAuthorModal = true;
    }

    public function saveAuthor(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $this->validate([
            'author_name' => 'required|string|max:255',
        ]);

        $data = [
            'author' => $validated['author_name'],
            'author_low' => mb_strtolower($validated['author_name']),
        ];

        if ($this->editingAuthor) {
            $this->editingAuthor->update($data);
            session()->flash('message', __('Author updated successfully.'));
        } else {
            Author::create($data);
            session()->flash('message', __('Author created successfully.'));
        }

        $this->showAuthorModal = false;
        $this->resetAuthorForm();
        $this->loadAuthors();
    }

    public function deleteAuthor(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $author = Author::withCount('publications')->findOrFail($id);

        if ($author->publications_count > 0) {
            session()->flash('error', __('Cannot delete author with associated publications.'));

            return;
        }

        $author->delete();
        session()->flash('message', __('Author deleted successfully.'));
        $this->loadAuthors();
    }

    private function resetAuthorForm(): void
    {
        $this->editingAuthor = null;
        $this->author_name = '';
        $this->author_name_low = '';
    }

    // ==================== PUBLISHERS ====================

    public function loadPublishers(): void
    {
        $this->publishers = Publisher::withCount('publications')->get();
    }

    public function createPublisher(): void
    {
        $this->resetPublisherForm();
        $this->showPublisherModal = true;
    }

    public function editPublisher(int $id): void
    {
        $this->editingPublisher = Publisher::findOrFail($id);
        $this->publisher_name_en = $this->editingPublisher->name_en;
        $this->publisher_name_ru = $this->editingPublisher->name_ru ?? '';
        $this->publisher_name_he = $this->editingPublisher->name_he ?? '';
        $this->publisher_slug = $this->editingPublisher->slug;
        $this->publisher_website = $this->editingPublisher->website ?? '';
        $this->showPublisherModal = true;
    }

    public function savePublisher(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $publisherId = $this->editingPublisher?->id;
        $validated = $this->validate([
            'publisher_name_en' => 'required|string|max:255',
            'publisher_name_ru' => 'nullable|string|max:255',
            'publisher_name_he' => 'nullable|string|max:255',
            'publisher_slug' => "required|string|max:255|unique:publishers,slug,{$publisherId}",
            'publisher_website' => 'nullable|url|max:255',
        ]);

        $data = [
            'name_en' => $validated['publisher_name_en'],
            'name_ru' => $validated['publisher_name_ru'],
            'name_he' => $validated['publisher_name_he'],
            'slug' => $validated['publisher_slug'],
            'website' => $validated['publisher_website'],
        ];

        if ($this->editingPublisher) {
            $this->editingPublisher->update($data);
            session()->flash('message', __('Publisher updated successfully.'));
        } else {
            Publisher::create($data);
            session()->flash('message', __('Publisher created successfully.'));
        }

        $this->showPublisherModal = false;
        $this->resetPublisherForm();
        $this->loadPublishers();
    }

    public function deletePublisher(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $publisher = Publisher::withCount('publications')->findOrFail($id);

        if ($publisher->publications_count > 0) {
            session()->flash('error', __('Cannot delete publisher with associated publications.'));

            return;
        }

        $publisher->delete();
        session()->flash('message', __('Publisher deleted successfully.'));
        $this->loadPublishers();
    }

    private function resetPublisherForm(): void
    {
        $this->editingPublisher = null;
        $this->publisher_name_en = '';
        $this->publisher_name_ru = '';
        $this->publisher_name_he = '';
        $this->publisher_slug = '';
        $this->publisher_website = '';
    }

    // ==================== MODAL CLOSE ====================

    public function closeModal(): void
    {
        $this->showContentTypeModal = false;
        $this->showGenreModal = false;
        $this->showThemeModal = false;
        $this->showCategoryModal = false;
        $this->showAuthorModal = false;
        $this->showPublisherModal = false;
    }

    public function render()
    {
        return view('livewire.admin.filtration-management')->layout('layouts.app');
    }
}
