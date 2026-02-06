<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Author;
use App\Models\Section;
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

    // Sections
    public $sections = [];

    public $parentSections = [];

    public bool $showSectionModal = false;

    public ?Section $editingSection = null;

    public string $section_name_en = '';

    public string $section_name_ru = '';

    public string $section_name_he = '';

    public string $section_slug = '';

    public ?int $section_parent_id = null;

    public int $section_sort_order = 0;

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

    // Delete Confirmation Modal
    public bool $showDeleteModal = false;

    public string $deleteType = '';

    public ?int $deleteId = null;

    public string $deleteName = '';

    public array $deletePublications = [];

    public ?int $replaceWithId = null;

    public array $replacementOptions = [];

    public int $deleteChildrenCount = 0;

    public array $publicationReplacements = [];

    public bool $applyToAll = true;

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
            'sections' => $this->loadSections(),
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

    // ==================== SECTIONS ====================

    public function loadSections(): void
    {
        $this->sections = Section::with('parent')
            ->withCount(['children', 'publications'])
            ->orderBy('sort_order')
            ->get();
    }

    public function createSection(): void
    {
        $this->resetSectionForm();
        $this->parentSections = Section::orderBy('name_en')->get();
        $this->showSectionModal = true;
    }

    public function editSection(int $id): void
    {
        $this->editingSection = Section::findOrFail($id);
        $this->section_name_en = $this->editingSection->name_en;
        $this->section_name_ru = $this->editingSection->name_ru ?? '';
        $this->section_name_he = $this->editingSection->name_he ?? '';
        $this->section_slug = $this->editingSection->slug;
        $this->section_parent_id = $this->editingSection->parent_id;
        $this->section_sort_order = $this->editingSection->sort_order ?? 0;
        $this->parentSections = Section::where('id', '!=', $id)->orderBy('name_en')->get();
        $this->showSectionModal = true;
    }

    public function saveSection(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $sectionId = $this->editingSection?->id;
        $validated = $this->validate([
            'section_name_en' => 'required|string|max:255',
            'section_name_ru' => 'nullable|string|max:255',
            'section_name_he' => 'nullable|string|max:255',
            'section_slug' => "required|string|max:255|unique:sections,slug,{$sectionId}",
            'section_parent_id' => 'nullable|exists:sections,id',
            'section_sort_order' => 'integer|min:0',
        ]);

        $data = [
            'name_en' => $validated['section_name_en'],
            'name_ru' => $validated['section_name_ru'],
            'name_he' => $validated['section_name_he'],
            'slug' => $validated['section_slug'],
            'parent_id' => $validated['section_parent_id'],
            'sort_order' => $validated['section_sort_order'],
        ];

        if ($this->editingSection) {
            $this->editingSection->update($data);
            session()->flash('message', __('Section updated successfully.'));
        } else {
            Section::create($data);
            session()->flash('message', __('Section created successfully.'));
        }

        $this->showSectionModal = false;
        $this->resetSectionForm();
        $this->loadSections();
    }

    public function deleteSection(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $section = Section::withCount(['children', 'publications'])->findOrFail($id);

        if ($section->children_count > 0) {
            session()->flash('error', __('Cannot delete section with child sections.'));

            return;
        }

        if ($section->publications_count > 0) {
            session()->flash('error', __('Cannot delete section with associated publications.'));

            return;
        }

        $section->delete();
        session()->flash('message', __('Section deleted successfully.'));
        $this->loadSections();
    }

    private function resetSectionForm(): void
    {
        $this->editingSection = null;
        $this->section_name_en = '';
        $this->section_name_ru = '';
        $this->section_name_he = '';
        $this->section_slug = '';
        $this->section_parent_id = null;
        $this->section_sort_order = 0;
        $this->parentSections = [];
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

    // ==================== DELETE CONFIRMATION ====================

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->replaceWithId = null;
        $this->deleteChildrenCount = 0;

        // Load entity details based on type
        switch ($type) {
            case 'content-type':
                $entity = ContentType::with('publications:id_publication,title')->findOrFail($id);
                $this->deleteName = $entity->name_en;
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = ContentType::where('id', '!=', $id)
                    ->orderBy('name_en')
                    ->get(['id', 'name_en'])
                    ->map(fn($c) => ['id' => $c->id, 'name' => $c->name_en])
                    ->toArray();
                break;

            case 'genre':
                $entity = Genre::with('publications:id_publication,title')->findOrFail($id);
                $this->deleteName = $entity->name_en;
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = Genre::where('id', '!=', $id)
                    ->orderBy('name_en')
                    ->get(['id', 'name_en'])
                    ->map(fn($g) => ['id' => $g->id, 'name' => $g->name_en])
                    ->toArray();
                break;

            case 'theme':
                $entity = Theme::with('publications:id_publication,title')->findOrFail($id);
                $this->deleteName = $entity->theme;
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = Theme::where('id_theme', '!=', $id)
                    ->orderBy('theme')
                    ->get(['id_theme', 'theme'])
                    ->map(fn($t) => ['id' => $t->id_theme, 'name' => $t->theme])
                    ->toArray();
                break;

            case 'section':
                $entity = Section::with(['publications:id_publication,title', 'children'])->findOrFail($id);
                $this->deleteName = $entity->name_en;
                $this->deleteChildrenCount = $entity->children->count();
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = Section::where('id', '!=', $id)
                    ->orderBy('name_en')
                    ->get(['id', 'name_en'])
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name_en])
                    ->toArray();
                break;

            case 'author':
                $entity = Author::with('publications:id_publication,title')->findOrFail($id);
                $this->deleteName = $entity->author;
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = Author::where('id_author', '!=', $id)
                    ->orderBy('author')
                    ->get(['id_author', 'author'])
                    ->map(fn($a) => ['id' => $a->id_author, 'name' => $a->author])
                    ->toArray();
                break;

            case 'publisher':
                $entity = Publisher::with('publications:id_publication,title')->findOrFail($id);
                $this->deleteName = $entity->name_en;
                $this->deletePublications = $entity->publications->map(fn($p) => [
                    'id' => $p->id_publication,
                    'title' => $p->title ?? __('Untitled'),
                ])->toArray();
                $this->replacementOptions = Publisher::where('id', '!=', $id)
                    ->orderBy('name_en')
                    ->get(['id', 'name_en'])
                    ->map(fn($p) => ['id' => $p->id, 'name' => $p->name_en])
                    ->toArray();
                break;
        }

        // Initialize per-publication replacements
        $this->publicationReplacements = [];
        foreach ($this->deletePublications as $pub) {
            $this->publicationReplacements[$pub['id']] = null;
        }

        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if (count($this->deletePublications) > 0 || $this->deleteChildrenCount > 0) {
            session()->flash('error', __('Невозможно удалить элемент с привязанными данными.'));
            $this->cancelDelete();
            return;
        }

        switch ($this->deleteType) {
            case 'content-type':
                ContentType::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Тип контента успешно удалён.'));
                $this->loadContentTypes();
                break;
            case 'genre':
                Genre::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Жанр успешно удалён.'));
                $this->loadGenres();
                break;
            case 'theme':
                Theme::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Тема успешно удалена.'));
                $this->loadThemes();
                break;
            case 'section':
                Section::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Раздел успешно удалён.'));
                $this->loadSections();
                break;
            case 'author':
                Author::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Автор успешно удалён.'));
                $this->loadAuthors();
                break;
            case 'publisher':
                Publisher::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Издатель успешно удалён.'));
                $this->loadPublishers();
                break;
        }

        $this->cancelDelete();
    }

    public function replaceAndDelete(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        // Validate: either global replacement or all per-publication replacements must be set
        if ($this->applyToAll) {
            if (!$this->replaceWithId) {
                session()->flash('error', __('Пожалуйста, выберите замену.'));
                return;
            }
        } else {
            $missingReplacements = array_filter($this->publicationReplacements, fn($v) => !$v);
            if (count($missingReplacements) > 0) {
                session()->flash('error', __('Пожалуйста, выберите замену для всех публикаций.'));
                return;
            }
        }

        switch ($this->deleteType) {
            case 'content-type':
                // ContentType uses HasMany, update FK directly
                if ($this->applyToAll) {
                    \App\Models\Publication::where('content_type_id', $this->deleteId)
                        ->update(['content_type_id' => $this->replaceWithId]);
                } else {
                    foreach ($this->publicationReplacements as $pubId => $newTypeId) {
                        \App\Models\Publication::where('id_publication', $pubId)
                            ->update(['content_type_id' => $newTypeId]);
                    }
                }
                ContentType::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Тип контента заменён и удалён.'));
                $this->loadContentTypes();
                break;

            case 'genre':
                $old = Genre::findOrFail($this->deleteId);
                foreach ($old->publications as $pub) {
                    $replaceId = $this->applyToAll ? $this->replaceWithId : ($this->publicationReplacements[$pub->id_publication] ?? null);
                    if ($replaceId && !$pub->genres()->where('genre_id', $replaceId)->exists()) {
                        $pub->genres()->attach($replaceId);
                    }
                }
                $old->publications()->detach();
                $old->delete();
                session()->flash('message', __('Жанр заменён и удалён.'));
                $this->loadGenres();
                break;

            case 'theme':
                $old = Theme::findOrFail($this->deleteId);
                foreach ($old->publications as $pub) {
                    $replaceId = $this->applyToAll ? $this->replaceWithId : ($this->publicationReplacements[$pub->id_publication] ?? null);
                    if ($replaceId && !$pub->themes()->where('id_theme', $replaceId)->exists()) {
                        $pub->themes()->attach($replaceId);
                    }
                }
                $old->publications()->detach();
                $old->delete();
                session()->flash('message', __('Тема заменена и удалена.'));
                $this->loadThemes();
                break;

            case 'section':
                $old = Section::findOrFail($this->deleteId);
                foreach ($old->publications as $pub) {
                    $replaceId = $this->applyToAll ? $this->replaceWithId : ($this->publicationReplacements[$pub->id_publication] ?? null);
                    if ($replaceId && !$pub->sections()->where('section_id', $replaceId)->exists()) {
                        $pub->sections()->attach($replaceId);
                    }
                }
                $old->publications()->detach();
                $old->delete();
                session()->flash('message', __('Раздел заменён и удалён.'));
                $this->loadSections();
                break;

            case 'author':
                $old = Author::findOrFail($this->deleteId);
                foreach ($old->publications as $pub) {
                    $replaceId = $this->applyToAll ? $this->replaceWithId : ($this->publicationReplacements[$pub->id_publication] ?? null);
                    if ($replaceId && !$pub->authors()->where('id_author', $replaceId)->exists()) {
                        $pub->authors()->attach($replaceId);
                    }
                }
                $old->publications()->detach();
                $old->delete();
                session()->flash('message', __('Автор заменён и удалён.'));
                $this->loadAuthors();
                break;

            case 'publisher':
                $old = Publisher::findOrFail($this->deleteId);
                foreach ($old->publications as $pub) {
                    $replaceId = $this->applyToAll ? $this->replaceWithId : ($this->publicationReplacements[$pub->id_publication] ?? null);
                    if ($replaceId && !$pub->publishers()->where('publisher_id', $replaceId)->exists()) {
                        $pub->publishers()->attach($replaceId);
                    }
                }
                $old->publications()->detach();
                $old->delete();
                session()->flash('message', __('Издатель заменён и удалён.'));
                $this->loadPublishers();
                break;
        }

        $this->cancelDelete();
    }

    public function detachAndDelete(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        switch ($this->deleteType) {
            case 'content-type':
                // For HasMany, set FK to null
                \App\Models\Publication::where('content_type_id', $this->deleteId)
                    ->update(['content_type_id' => null]);
                ContentType::findOrFail($this->deleteId)->delete();
                session()->flash('message', __('Тип контента отвязан и удалён.'));
                $this->loadContentTypes();
                break;

            case 'genre':
                $entity = Genre::findOrFail($this->deleteId);
                $entity->publications()->detach();
                $entity->delete();
                session()->flash('message', __('Жанр отвязан и удалён.'));
                $this->loadGenres();
                break;

            case 'theme':
                $entity = Theme::findOrFail($this->deleteId);
                $entity->publications()->detach();
                $entity->delete();
                session()->flash('message', __('Тема отвязана и удалена.'));
                $this->loadThemes();
                break;

            case 'section':
                $entity = Section::findOrFail($this->deleteId);
                $entity->publications()->detach();
                $entity->delete();
                session()->flash('message', __('Раздел отвязан и удалён.'));
                $this->loadSections();
                break;

            case 'author':
                $entity = Author::findOrFail($this->deleteId);
                $entity->publications()->detach();
                $entity->delete();
                session()->flash('message', __('Автор отвязан и удалён.'));
                $this->loadAuthors();
                break;

            case 'publisher':
                $entity = Publisher::findOrFail($this->deleteId);
                $entity->publications()->detach();
                $entity->delete();
                session()->flash('message', __('Издатель отвязан и удалён.'));
                $this->loadPublishers();
                break;
        }

        $this->cancelDelete();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteType = '';
        $this->deleteId = null;
        $this->deleteName = '';
        $this->deletePublications = [];
        $this->replaceWithId = null;
        $this->replacementOptions = [];
        $this->deleteChildrenCount = 0;
        $this->publicationReplacements = [];
        $this->applyToAll = true;
    }

    // ==================== MODAL CLOSE ====================

    public function closeModal(): void
    {
        $this->showContentTypeModal = false;
        $this->showGenreModal = false;
        $this->showThemeModal = false;
        $this->showSectionModal = false;
        $this->showAuthorModal = false;
        $this->showPublisherModal = false;
    }

    public function render()
    {
        return view('livewire.admin.filtration-management')->layout('layouts.app');
    }
}
