<?php

declare(strict_types=1);

namespace App\Livewire\Publications;

use App\Models\CustomField;
use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PublicationPreview extends Component
{
    public Publication $publication;

    public bool $isAuthenticated = false;

    public bool $isAdmin = false;

    public ?object $primaryFile = null;

    /**
     * Mount the component with publication ID.
     */
    public function mount(int $id): void
    {
        $this->publication = Publication::with([
            'authors',
            'genres',
            'themes',
            'files',
            'contentType',
            'customFieldValues.customField',
            'sections',
            'publishers',
        ])->findOrFail($id);

        // Cache the primary file to avoid repeated queries
        $this->primaryFile = $this->publication->files()
            ->where(function($query) {
                $query->where('file_type', 'content')
                      ->orWhereNull('file_type');
            })
            ->first();

        $this->isAuthenticated = Auth::check();
        $this->isAdmin = Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get custom fields with values that are visible to current user.
     */
    public function getVisibleCustomFields(): array
    {
        if (! $this->publication->content_type_id) {
            return [];
        }

        $visibilityFilter = $this->isAdmin ? ['public', 'admin_only'] : ['public'];

        $customFields = CustomField::where('content_type_id', $this->publication->content_type_id)
            ->whereIn('visibility', $visibilityFilter)
            ->orderedBySortOrder()
            ->get();

        $values = $this->publication->customFieldValues()
            ->with('customField')
            ->get()
            ->keyBy('custom_field_id');

        return $customFields->map(function ($field) use ($values) {
            $value = $values->has($field->id) ? $values->get($field->id)->getTypedValue() : null;

            // Only return fields that have values
            if ($value !== null && $value !== '') {
                return [
                    'field' => $field,
                    'value' => $value,
                ];
            }

            return null;
        })->filter()->values()->toArray();
    }

    /**
     * Get cover image URL if available.
     */
    public function getCoverImageUrl(): ?string
    {
        $coverFile = $this->publication->files()
            ->where('file_type', 'cover')
            ->first();

        if ($coverFile && $coverFile->file_name) {
            // Generate public URL for cover image (no auth required)
            $encodedFilename = rtrim(strtr(base64_encode($coverFile->file_name), '+/', '-_'), '=');

            return route('covers.serve', [
                'publication' => $this->publication->id_publication,
                'filename' => $encodedFilename,
            ]);
        }

        return null;
    }

    /**
     * Get primary content file.
     */
    public function getPrimaryFile()
    {
        return $this->primaryFile;
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSize(?int $bytes = null): string
    {
        $bytes = $bytes ?? $this->getPrimaryFile()?->file_size_bytes ?? 0;

        if ($bytes === 0) {
            return __('Unknown');
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Get file format/extension.
     */
    public function getFileExtension(): string
    {
        $file = $this->getPrimaryFile();
        if (! $file) {
            return __('Unknown');
        }

        return strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION));
    }

    /**
     * Check if publication is published.
     */
    public function isPublished(): bool
    {
        return $this->publication->status === 'published';
    }

    /**
     * Get description (truncated to 500 chars).
     */
    public function getTruncatedDescription(): string
    {
        $description = $this->publication->description ?? '';

        if (strlen($description) > 500) {
            return substr($description, 0, 500).'...';
        }

        return $description;
    }

    /**
     * Get genres for display (limit to 3, show "+X more" for rest).
     */
    public function getDisplayGenres()
    {
        $genres = $this->publication->genres()->limit(3)->get();
        $remaining = max(0, $this->publication->genres()->count() - 3);

        return [
            'genres' => $genres,
            'remaining' => $remaining,
        ];
    }

    /**
     * Get all metadata sections organized for better presentation.
     */
    public function getMetadataSections(): array
    {
        $sections = [];

        // Basic Info Section
        if ($this->publication->issue_year || $this->publication->publishers->count() > 0) {
            $sections['basic'] = [
                'title' => __('Publication Details'),
                'icon' => 'book-open',
                'items' => array_filter([
                    'year' => $this->publication->issue_year ? [
                        'label' => __('Year'),
                        'value' => $this->publication->issue_year,
                        'icon' => 'calendar',
                    ] : null,
                    'publishers' => $this->publication->publishers->count() > 0 ? [
                        'label' => __('Publisher'),
                        'value' => $this->publication->publishers->map(fn($p) => $p->{'name_' . app()->getLocale()} ?? $p->name_en)->join(', '),
                        'icon' => 'building-library',
                    ] : null,
                ])
            ];
        }

        // Authors Section
        if ($this->publication->authors->count() > 0) {
            $sections['authors'] = [
                'title' => __('Authors'),
                'icon' => 'users',
                'items' => [
                    'authors' => [
                        'label' => __('Written by'),
                        'value' => $this->publication->authors->map(fn($a) => $a->author)->join(', '),
                        'count' => $this->publication->authors->count(),
                        'icon' => 'pencil',
                    ]
                ]
            ];
        }

        // Description Section
        if ($this->publication->description) {
            $sections['description'] = [
                'title' => __('About this publication'),
                'icon' => 'document-text',
                'content' => $this->getTruncatedDescription(),
                'isFullDescription' => strlen($this->publication->description ?? '') <= 500,
            ];
        }

        // Genres & Themes Section
        $genreData = $this->getDisplayGenres();
        if ($genreData['genres']->count() > 0 || $this->publication->themes->count() > 0) {
            $sections['categories'] = [
                'title' => __('Categories & Tags'),
                'icon' => 'tag',
                'genres' => [
                    'items' => $genreData['genres'],
                    'remaining' => $genreData['remaining'],
                ],
                'themes' => $this->publication->themes,
            ];
        }

        // Sections
        if ($this->publication->sections->count() > 0) {
            $sections['sections'] = [
                'title' => __('Sections'),
                'icon' => 'folder',
                'items' => $this->publication->sections,
            ];
        }

        // Custom Fields Section
        $customFields = $this->getVisibleCustomFields();
        if (!empty($customFields)) {
            $sections['custom'] = [
                'title' => __('Additional Information'),
                'icon' => 'information-circle',
                'fields' => $customFields,
            ];
        }

        return $sections;
    }

    /**
     * Get file type icon and color based on extension.
     */
    public function getFileTypeIcon(): array
    {
        $extension = strtolower($this->getFileExtension());

        $fileTypes = [
            'pdf' => ['icon' => 'document-text', 'color' => 'red', 'emoji' => '📄'],
            'epub' => ['icon' => 'book', 'color' => 'blue', 'emoji' => '📖'],
            'doc' => ['icon' => 'document', 'color' => 'blue', 'emoji' => '📝'],
            'docx' => ['icon' => 'document', 'color' => 'blue', 'emoji' => '📝'],
            'txt' => ['icon' => 'document-text', 'color' => 'gray', 'emoji' => '📃'],
            'rtf' => ['icon' => 'document-text', 'color' => 'purple', 'emoji' => '📄'],
            'odt' => ['icon' => 'document', 'color' => 'orange', 'emoji' => '📝'],
            'mobi' => ['icon' => 'book', 'color' => 'teal', 'emoji' => '📱'],
            'azw' => ['icon' => 'book', 'color' => 'amber', 'emoji' => '📱'],
            'azw3' => ['icon' => 'book', 'color' => 'amber', 'emoji' => '📱'],
        ];

        return $fileTypes[$extension] ?? ['icon' => 'document', 'color' => 'gray', 'emoji' => '📄'];
    }

    /**
     * Get publication statistics for display.
     */
    public function getPublicationStats(): array
    {
        return [
            'authors' => $this->publication->authors->count(),
            'genres' => $this->publication->genres->count(),
            'themes' => $this->publication->themes->count(),
            'sections' => $this->publication->sections->count(),
            'fileSize' => $this->getFormattedFileSize(),
            'fileFormat' => $this->getFileExtension(),
        ];
    }

    /**
     * Get content type badge styling.
     */
    public function getContentTypeBadge(): array
    {
        if (!$this->publication->contentType) {
            return ['color' => 'gray', 'icon' => 'document'];
        }

        $typeColors = [
            'book' => 'indigo',
            'article' => 'blue',
            'journal' => 'purple',
            'thesis' => 'pink',
            'report' => 'teal',
            'manuscript' => 'amber',
        ];

        $typeName = strtolower($this->publication->contentType->name_en ?? '');

        foreach ($typeColors as $type => $color) {
            if (str_contains($typeName, $type)) {
                return ['color' => $color, 'icon' => $this->publication->contentType->icon ?? 'document'];
            }
        }

        return ['color' => 'gray', 'icon' => $this->publication->contentType->icon ?? 'document'];
    }

    /**
     * Get reading time estimate (rough calculation).
     */
    public function getReadingTime(): string
    {
        $file = $this->getPrimaryFile();
        if (!$file || !$file->file_size_bytes) {
            return __('Unknown');
        }

        // Rough estimate: 1 KB ≈ 0.5-1 minute of reading time
        $readingMinutes = max(1, round($file->file_size_bytes / 1024 * 0.75));

        if ($readingMinutes < 60) {
            return $readingMinutes . ' ' . ($readingMinutes === 1 ? __('minute') : __('minutes'));
        }

        $hours = floor($readingMinutes / 60);
        $minutes = $readingMinutes % 60;

        return $hours . ' ' . ($hours === 1 ? __('hour') : __('hours')) .
               ($minutes > 0 ? ' ' . $minutes . ' ' . __('minutes') : '');
    }

    /**
     * Get file metadata for this publication (for admin management).
     */
    public function getFileMetadataForAdmin()
    {
        return \App\Models\FileMetadata::where('publication_id', $this->publication->id_publication)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function render()
    {
        return view('livewire.publications.publication-preview');
    }
}
