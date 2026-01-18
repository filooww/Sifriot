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

    /**
     * Mount the component with publication ID.
     */
    public function mount(int $id): void
    {
        $this->publication = Publication::with([
            'authors',
            'publishing',
            'genres',
            'themeSet',
            'files',
            'contentType',
            'customFieldValues.customField',
        ])->findOrFail($id);

        $this->isAuthenticated = Auth::check();
        $this->isAdmin = Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get custom fields with values that are visible to current user.
     */
    public function getVisibleCustomFields(): array
    {
        if (!$this->publication->content_type_id) {
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
        return $this->publication->files()
            ->where('file_type', 'content')
            ->orWhere('file_type', null)
            ->first();
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

    public function render()
    {
        return view('livewire.publications.publication-preview');
    }
}
