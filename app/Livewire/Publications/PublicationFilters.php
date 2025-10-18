<?php

declare(strict_types=1);

namespace App\Livewire\Publications;

use App\Models\Author;
use App\Models\Category;
use App\Models\Theme;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PublicationFilters extends Component
{
    public bool $hideAdminFilters = false;

    public array $selectedCategories = [];

    public array $selectedAuthors = [];

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public array $selectedGenres = [];

    public array $textSizeRange = [0, 500000];

    public ?string $alphabeticalSort = null;

    public array $publicationStatus = [];

    public string $authorSearchQuery = '';

    protected $queryString = [
        'selectedCategories' => ['as' => 'cat', 'except' => []],
        'selectedAuthors' => ['as' => 'auth', 'except' => []],
        'dateFrom' => ['as' => 'from', 'except' => null],
        'dateTo' => ['as' => 'to', 'except' => null],
        'selectedGenres' => ['as' => 'genre', 'except' => []],
        'textSizeRange' => ['as' => 'size', 'except' => [0, 500000]],
        'alphabeticalSort' => ['as' => 'sort', 'except' => null],
        'publicationStatus' => ['as' => 'status', 'except' => []],
    ];

    public function updatedSelectedCategories(): void
    {
        $this->emitFilters();
    }

    public function updatedSelectedAuthors(): void
    {
        $this->emitFilters();
    }

    public function updatedDateFrom(): void
    {
        $this->emitFilters();
    }

    public function updatedDateTo(): void
    {
        $this->emitFilters();
    }

    public function updatedSelectedGenres(): void
    {
        $this->emitFilters();
    }

    public function updatedTextSizeRange(): void
    {
        $this->emitFilters();
    }

    public function updatedAlphabeticalSort(): void
    {
        $this->emitFilters();
    }

    public function updatedPublicationStatus(): void
    {
        $this->emitFilters();
    }

    public function clearAllFilters(): void
    {
        $this->selectedCategories = [];
        $this->selectedAuthors = [];
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->selectedGenres = [];
        $this->textSizeRange = [0, 500000];
        $this->alphabeticalSort = null;
        $this->publicationStatus = [];
        $this->authorSearchQuery = '';

        $this->emitFilters();
    }

    public function removeFilter(string $filterType, mixed $value): void
    {
        match ($filterType) {
            'category' => $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$value])),
            'author' => $this->selectedAuthors = array_values(array_diff($this->selectedAuthors, [$value])),
            'dateFrom' => $this->dateFrom = null,
            'dateTo' => $this->dateTo = null,
            'genre' => $this->selectedGenres = array_values(array_diff($this->selectedGenres, [$value])),
            'textSize' => $this->textSizeRange = [0, 500000],
            'alphabetical' => $this->alphabeticalSort = null,
            'status' => $this->publicationStatus = array_values(array_diff($this->publicationStatus, [$value])),
            default => null,
        };

        $this->emitFilters();
    }

    #[Computed]
    public function appliedFilters(): array
    {
        $filters = [];

        foreach ($this->selectedCategories as $categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $filters[] = [
                    'type' => 'category',
                    'value' => $categoryId,
                    'label' => $category->localized_name,
                ];
            }
        }

        foreach ($this->selectedAuthors as $authorId) {
            $author = Author::find($authorId);
            if ($author) {
                $filters[] = [
                    'type' => 'author',
                    'value' => $authorId,
                    'label' => $author->author_name,
                ];
            }
        }

        if ($this->dateFrom) {
            $filters[] = [
                'type' => 'dateFrom',
                'value' => $this->dateFrom,
                'label' => __('From').': '.$this->dateFrom,
            ];
        }

        if ($this->dateTo) {
            $filters[] = [
                'type' => 'dateTo',
                'value' => $this->dateTo,
                'label' => __('To').': '.$this->dateTo,
            ];
        }

        foreach ($this->selectedGenres as $genreId) {
            $theme = Theme::find($genreId);
            if ($theme) {
                $filters[] = [
                    'type' => 'genre',
                    'value' => $genreId,
                    'label' => $theme->theme,
                ];
            }
        }

        if ($this->textSizeRange !== [0, 500000]) {
            $filters[] = [
                'type' => 'textSize',
                'value' => $this->textSizeRange,
                'label' => __('Text Size').': '.number_format($this->textSizeRange[0]).'-'.number_format($this->textSizeRange[1]).' '.__('words'),
            ];
        }

        if ($this->alphabeticalSort) {
            $filters[] = [
                'type' => 'alphabetical',
                'value' => $this->alphabeticalSort,
                'label' => __('Sort').': '.__($this->alphabeticalSort),
            ];
        }

        foreach ($this->publicationStatus as $status) {
            $filters[] = [
                'type' => 'status',
                'value' => $status,
                'label' => __('Status').': '.__(ucfirst($status)),
            ];
        }

        return $filters;
    }

    #[Computed]
    public function authorSearchResults(): array
    {
        if (strlen($this->authorSearchQuery) < 2) {
            return [];
        }

        return Author::where('author_name', 'like', '%'.$this->authorSearchQuery.'%')
            ->limit(10)
            ->get()
            ->map(fn ($author) => [
                'id' => $author->id_author,
                'name' => $author->author_name,
            ])
            ->toArray();
    }

    #[Computed]
    public function categories(): array
    {
        return Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function genres(): array
    {
        return Theme::orderBy('theme')
            ->limit(50)
            ->get()
            ->toArray();
    }

    protected function emitFilters(): void
    {
        $this->dispatch('filtersChanged', filters: [
            'categories' => $this->selectedCategories,
            'authors' => $this->selectedAuthors,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'genres' => $this->selectedGenres,
            'textSizeRange' => $this->textSizeRange,
            'alphabeticalSort' => $this->alphabeticalSort,
            'publicationStatus' => $this->publicationStatus,
        ]);
    }

    public function render()
    {
        return view('livewire.publications.publication-filters');
    }
}
