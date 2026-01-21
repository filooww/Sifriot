<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Events\MetadataConfirmed;
use App\Models\Author;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\File;
use App\Models\FileMetadata;
use App\Models\Genre;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Publishing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class MetadataReviewForm extends Component
{
    use WithFileUploads;

    public ?FileMetadata $fileMetadata = null;

    public string $title = '';

    public array $authors = [''];

    public ?int $publicationYear = null;

    public string $publisher = '';

    public array $genres = [''];

    public string $theme = '';

    public ?int $contentTypeId = null;

    public string $description = '';

    public $coverImage = null;

    public bool $useExtracted = false;

    public bool $useManual = false;

    public array $confidenceScores = [];

    public ?string $extractionStatus = null;

    public ?string $extractionMethod = null;

    public ?string $errorMessage = null;

    public bool $showDetails = false;

    public bool $showFilePreview = false;

    public array $customFieldValues = [];

    public $customFields = [];

    // Categories and Publishers (multi-select)
    public array $selectedCategories = [];

    public array $selectedPublishers = [];

    public string $categorySearchQuery = '';

    public string $publisherSearchQuery = '';

    /**
     * Updated hook: Auto-save cover image when uploaded
     * This ensures the image is persisted even if user closes modal without clicking save button
     */
    #[\Livewire\Attributes\On('updated:coverImage')]
    public function updatedCoverImage(): void
    {
        if ($this->coverImage && $this->fileMetadata) {
            try {
                // Validate before saving
                $this->validate(['coverImage' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

                // Get publication
                $publication = Publication::with('files')->find($this->fileMetadata->file_id);
                if (! $publication) {
                    throw new \Exception('Publication not found');
                }

                // Generate unique filename
                $originalName = $this->coverImage->getClientOriginalName();
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $uniqueName = uniqid().'_'.time().'.'.$extension;

                // Store the file
                $filePath = $this->coverImage->storeAs('covers', $uniqueName, 'public');

                // Get next ord_num for this publication
                $nextOrdNum = File::where('id_publication', $publication->id_publication)
                    ->max('ord_num') + 1;

                // Create File record for cover image
                File::create([
                    'id_publication' => $publication->id_publication,
                    'ord_num' => $nextOrdNum,
                    'file_name' => $originalName,
                    'file_name_low' => mb_strtolower($originalName),
                    'file_size' => (string) $this->coverImage->getSize(),
                    'file_size_bytes' => $this->coverImage->getSize(),
                    'mime_type' => $this->coverImage->getMimeType(),
                    'file_type' => 'cover',
                    'file_path' => $filePath,
                    'file_source' => 'manual_upload',
                ]);

                // Show success notification
                $this->dispatch('notify', message: 'Cover image saved successfully!', type: 'success')->to('admin.metadata-review-dashboard');
            } catch (\Exception $e) {
                Log::error('Failed to auto-save cover image', ['error' => $e->getMessage()]);
                $this->dispatch('notify', message: 'Failed to save cover image: '.$e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
            }
        }
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'authors' => 'array|min:1',
        'authors.*' => 'string|max:255',
        'publicationYear' => 'nullable|integer|min:1000|max:2100',
        'publisher' => 'nullable|string|max:255',
        'genres' => 'array',
        'genres.*' => 'string|max:255',
        'theme' => 'nullable|string|max:255',
        'contentTypeId' => 'nullable|integer|exists:content_types,id',
        'description' => 'nullable|string|max:1000',
        'coverImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
    ];

    /**
     * Mount the component with FileMetadata.
     */
    public function mount(FileMetadata $fileMetadata): void
    {
        $this->fileMetadata = $fileMetadata;
        $this->loadMetadata();
    }

    /**
     * Load metadata into form fields.
     */
    private function loadMetadata(): void
    {
        if (! $this->fileMetadata) {
            return;
        }

        $this->extractionStatus = $this->fileMetadata->status;
        $this->extractionMethod = $this->fileMetadata->extraction_method;
        $this->errorMessage = $this->fileMetadata->error_message;
        $this->confidenceScores = $this->fileMetadata->confidence_scores ?? [];

        // Load extracted data (for processed, confirmed, and rejected statuses)
        if ($this->fileMetadata->status === 'processed' || $this->fileMetadata->status === 'confirmed' || $this->fileMetadata->status === 'rejected') {
            $this->title = $this->fileMetadata->getTitle() ?? '';
            $this->authors = ! empty($this->fileMetadata->getAuthors())
                ? $this->fileMetadata->getAuthors()
                : [''];
            $this->publicationYear = $this->fileMetadata->getPublicationYear();
            $this->publisher = $this->fileMetadata->getPublisher() ?? '';
            $this->genres = ! empty($this->fileMetadata->getGenres())
                ? $this->fileMetadata->getGenres()
                : [''];
            $this->useExtracted = true;
        }

        // Load description from publication if confirmed
        if ($this->fileMetadata->status === 'confirmed') {
            // Use with('files') to eager-load files relationship including cover images
            $publication = Publication::with('files')->find($this->fileMetadata->file_id);
            if ($publication) {
                $this->description = $publication->description ?? '';
                $this->contentTypeId = $publication->content_type_id;
            }
        }

        // Load existing categories and publishers for the publication
        $this->loadCategoriesAndPublishers();

        // Load custom fields if content type is selected
        $this->loadCustomFields();
    }

    /**
     * Load custom fields based on selected content type.
     */
    public function loadCustomFields(): void
    {
        if (! $this->contentTypeId) {
            $this->customFields = [];

            return;
        }

        $this->customFields = CustomField::where('content_type_id', $this->contentTypeId)
            ->orderedBySortOrder()
            ->get();

        // Load existing values if publication exists
        if ($this->fileMetadata && $this->fileMetadata->file_id) {
            $publication = Publication::find($this->fileMetadata->file_id);
            if ($publication) {
                foreach ($this->customFields as $field) {
                    $value = $publication->customFieldValues()
                        ->where('custom_field_id', $field->id)
                        ->first();

                    if ($value) {
                        $this->customFieldValues[$field->field_name] = $value->getTypedValue();
                    }
                }
            }
        }
    }

    /**
     * Load existing categories and publishers for the publication.
     */
    private function loadCategoriesAndPublishers(): void
    {
        if (! $this->fileMetadata || ! $this->fileMetadata->file_id) {
            return;
        }

        $publication = Publication::with(['categories', 'publishers'])->find($this->fileMetadata->file_id);
        if (! $publication) {
            return;
        }

        // Load existing category IDs
        $this->selectedCategories = $publication->categories->pluck('id')->toArray();

        // Load existing publisher IDs
        $this->selectedPublishers = $publication->publishers->pluck('id')->toArray();
    }

    /**
     * Listen for content type changes and reload custom fields.
     */
    public function updatedContentTypeId(): void
    {
        $this->loadCustomFields();
    }

    /**
     * Get the current cover image URL if one exists.
     */
    public function getCurrentCoverImageUrl(): ?string
    {
        if (! $this->fileMetadata) {
            return null;
        }

        $publication = Publication::with('files')->find($this->fileMetadata->file_id);
        if (! $publication) {
            return null;
        }

        $coverFile = $publication->files()
            ->where('file_type', 'cover')
            ->first();

        if ($coverFile && $coverFile->file_name) {
            // Generate public URL for cover image (no auth required)
            $encodedFilename = rtrim(strtr(base64_encode($coverFile->file_name), '+/', '-_'), '=');

            return route('covers.serve', [
                'publication' => $publication->id_publication,
                'filename' => $encodedFilename,
            ]);
        }

        return null;
    }

    /**
     * Add a new author field.
     */
    public function addAuthor(): void
    {
        $this->authors[] = '';
    }

    /**
     * Remove an author field.
     */
    public function removeAuthor(int $index): void
    {
        unset($this->authors[$index]);
        $this->authors = array_values($this->authors);
    }

    /**
     * Add a new genre field.
     */
    public function addGenre(): void
    {
        $this->genres[] = '';
    }

    /**
     * Remove a genre field.
     */
    public function removeGenre(int $index): void
    {
        unset($this->genres[$index]);
        $this->genres = array_values($this->genres);
    }

    /**
     * Confirm extraction with form data and save to normalized tables.
     */
    public function confirmExtraction(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Clean empty values
            $cleanedAuthors = array_filter($this->authors, fn ($author) => ! empty(trim($author)));
            $cleanedGenres = array_filter($this->genres, fn ($genre) => ! empty(trim($genre)));

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find($this->fileMetadata->file_id);

            if (! $publication) {
                throw new \Exception('Publication not found for this file metadata');
            }

            // Save authors to normalized tables
            foreach ($cleanedAuthors as $authorName) {
                $trimmedName = trim($authorName);
                $author = Author::firstOrCreate(
                    ['author' => $trimmedName, 'author_low' => mb_strtolower($trimmedName)]
                );
                $publication->authors()->syncWithoutDetaching([$author->id_author]);
            }

            // Save publisher if provided
            if (! empty($this->publisher)) {
                $trimmedPublisher = trim($this->publisher);
                $publisher = Publishing::firstOrCreate(
                    ['publishing' => $trimmedPublisher, 'publishing_low' => mb_strtolower($trimmedPublisher)]
                );
                $publication->update(['id_publishing' => $publisher->id_publishing]);
            }

            // Save genres to normalized tables
            foreach ($cleanedGenres as $genreName) {
                $trimmedGenre = trim($genreName);
                $genre = Genre::firstOrCreate(
                    ['slug' => Str::slug($trimmedGenre), 'name_en' => $trimmedGenre]
                );
                $publication->genres()->syncWithoutDetaching([$genre->id]);
            }

            // Sync categories
            if (! empty($this->selectedCategories)) {
                $publication->categories()->sync($this->selectedCategories);
            }

            // Sync publishers (new Publisher model)
            if (! empty($this->selectedPublishers)) {
                $publication->publishers()->sync($this->selectedPublishers);
            }

            // Note: Cover image is auto-saved via updatedCoverImage() hook when user selects file
            // No need to save it again here

            // Update publication with new metadata and set status to pending
            $publication->update([
                'title' => $this->title,
                'title_low' => mb_strtolower($this->title),
                'issue_year' => $this->publicationYear ? (string) $this->publicationYear : null,
                'content_type_id' => $this->contentTypeId,
                'description' => $this->description ?: null,
                'status' => 'pending',
            ]);

            // Save custom field values
            foreach ($this->customFieldValues as $fieldName => $value) {
                if ($value !== null && $value !== '') {
                    $publication->setCustomFieldValue($fieldName, $value);
                }
            }

            // Update FileMetadata to confirmed state
            $this->fileMetadata->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'extracted_data' => [
                    'title' => [
                        'value' => $this->title,
                        'confidence' => $this->confidenceScores['title'] ?? 0.8,
                    ],
                    'authors' => array_map(
                        fn ($author) => [
                            'value' => trim($author),
                            'confidence' => $this->confidenceScores['authors'] ?? 0.8,
                        ],
                        $cleanedAuthors
                    ),
                    'publication_year' => $this->publicationYear ? [
                        'value' => $this->publicationYear,
                        'confidence' => $this->confidenceScores['publication_year'] ?? 0.8,
                    ] : null,
                    'publisher' => $this->publisher ? [
                        'value' => $this->publisher,
                        'confidence' => $this->confidenceScores['publisher'] ?? 0.8,
                    ] : null,
                    'genres' => array_map(
                        fn ($genre) => [
                            'value' => trim($genre),
                            'confidence' => $this->confidenceScores['genres'] ?? 0.8,
                        ],
                        $cleanedGenres
                    ),
                    'theme' => $this->theme ? [
                        'value' => trim($this->theme),
                        'confidence' => 0.95,
                    ] : null,
                ],
            ]);

            DB::commit();

            Log::channel('folder_scan')->info('Metadata extraction confirmed and saved to normalized tables', [
                'file_metadata_id' => $this->fileMetadata->id,
                'file_name' => $this->fileMetadata->file_name,
                'publication_id' => $publication->id_publication,
                'title' => $this->title,
                'authors_count' => count($cleanedAuthors),
                'genres_count' => count($cleanedGenres),
            ]);

            // Fire event to auto-apply metadata to Publication
            MetadataConfirmed::dispatch($this->fileMetadata);

            // Reset coverImage to clear the temporary file
            $this->coverImage = null;

            // Close modal and refresh parent queue
            $this->dispatch('refresh-metadata-queue')->to('admin.metadata-review-dashboard');
            $this->dispatch('notify', message: 'Metadata confirmed and saved successfully!', type: 'success')->to('admin.metadata-review-dashboard');

            // Close the modal
            $this->dispatch('close-metadata-modal')->to('admin.metadata-review-dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm metadata', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_metadata_id' => $this->fileMetadata->id ?? null,
            ]);
            \Log::channel('folder_scan')->error('Metadata confirmation failed: '.$e->getMessage());
            $this->dispatch('notify', message: 'Failed to confirm metadata: '.$e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
        }
    }

    /**
     * Reject extraction and keep form for manual entry.
     */
    public function rejectExtraction(): void
    {
        try {
            $this->fileMetadata->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            Log::channel('folder_scan')->info('Metadata extraction rejected by admin', [
                'file_metadata_id' => $this->fileMetadata->id,
                'file_name' => $this->fileMetadata->file_name,
            ]);

            $this->useExtracted = false;
            $this->useManual = true;
            $this->resetForm();

            $this->dispatch('notify', message: 'Extraction rejected. Enter metadata manually.', type: 'info');
        } catch (\Exception $e) {
            Log::error('Failed to reject metadata extraction', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'Failed to reject extraction: '.$e->getMessage(), type: 'error');
        }
    }

    /**
     * Update metadata for rejected publications without marking as confirmed.
     * This allows editing rejected items and saving changes.
     */
    public function updateMetadata(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $cleanedAuthors = array_filter($this->authors, fn ($author) => ! empty(trim($author)));
            $cleanedGenres = array_filter($this->genres, fn ($genre) => ! empty(trim($genre)));

            // Get or create Publication
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find($this->fileMetadata->file_id);

            if (! $publication) {
                throw new \Exception('Publication not found for this file metadata');
            }

            // Save authors to normalized tables
            foreach ($cleanedAuthors as $authorName) {
                $trimmedName = trim($authorName);
                $author = Author::firstOrCreate(
                    ['author' => $trimmedName, 'author_low' => mb_strtolower($trimmedName)]
                );
                $publication->authors()->syncWithoutDetaching([$author->id_author]);
            }

            // Save publisher if provided
            if (! empty($this->publisher)) {
                $trimmedPublisher = trim($this->publisher);
                $publisher = Publishing::firstOrCreate(
                    ['publishing' => $trimmedPublisher, 'publishing_low' => mb_strtolower($trimmedPublisher)]
                );
                $publication->update(['id_publishing' => $publisher->id_publishing]);
            }

            // Save genres to normalized tables
            foreach ($cleanedGenres as $genreName) {
                $trimmedGenre = trim($genreName);
                $genre = Genre::firstOrCreate(
                    ['slug' => Str::slug($trimmedGenre), 'name_en' => $trimmedGenre]
                );
                $publication->genres()->syncWithoutDetaching([$genre->id]);
            }

            // Sync categories
            $publication->categories()->sync($this->selectedCategories);

            // Sync publishers (new Publisher model)
            $publication->publishers()->sync($this->selectedPublishers);

            // Note: Cover image is auto-saved via updatedCoverImage() hook when user selects file
            // No need to save it again here

            // Update publication with new metadata (but NOT status)
            $publication->update([
                'title' => $this->title,
                'title_low' => mb_strtolower($this->title),
                'issue_year' => $this->publicationYear ? (string) $this->publicationYear : null,
                'content_type_id' => $this->contentTypeId,
                'description' => $this->description ?: null,
            ]);

            // Update FileMetadata to reflect changes but stay as rejected
            // (Don't change status to confirmed - just update the data)
            $this->fileMetadata->update([
                'extracted_data' => [
                    'title' => ['value' => $this->title, 'confidence' => 1.0],
                    'authors' => array_map(
                        fn ($author) => ['value' => trim($author), 'confidence' => 1.0],
                        $cleanedAuthors
                    ),
                    'publication_year' => $this->publicationYear ? ['value' => $this->publicationYear, 'confidence' => 1.0] : null,
                    'publisher' => $this->publisher ? ['value' => $this->publisher, 'confidence' => 1.0] : null,
                    'genres' => array_map(
                        fn ($genre) => ['value' => trim($genre), 'confidence' => 1.0],
                        $cleanedGenres
                    ),
                    'theme' => $this->theme ? ['value' => $this->theme, 'confidence' => 1.0] : null,
                ],
            ]);

            DB::commit();

            // Reset coverImage to clear the temporary file
            $this->coverImage = null;

            $this->dispatch('notify', message: 'Metadata updated successfully', type: 'success')->to('admin.metadata-review-dashboard');
            $this->dispatch('refresh-metadata-queue')->to('admin.metadata-review-dashboard');

            // Close modal
            $this->dispatch('close-metadata-modal')->to('admin.metadata-review-dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update metadata', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'Failed to update metadata: '.$e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
        }
    }

    /**
     * Save manual entry without extracted data and save to normalized tables.
     */
    public function saveManualEntry(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $cleanedAuthors = array_filter($this->authors, fn ($author) => ! empty(trim($author)));
            $cleanedGenres = array_filter($this->genres, fn ($genre) => ! empty(trim($genre)));

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find($this->fileMetadata->file_id);

            if (! $publication) {
                throw new \Exception('Publication not found for this file metadata');
            }

            // Save authors to normalized tables
            foreach ($cleanedAuthors as $authorName) {
                $trimmedName = trim($authorName);
                $author = Author::firstOrCreate(
                    ['author' => $trimmedName, 'author_low' => mb_strtolower($trimmedName)]
                );
                $publication->authors()->syncWithoutDetaching([$author->id_author]);
            }

            // Save publisher if provided
            if (! empty($this->publisher)) {
                $trimmedPublisher = trim($this->publisher);
                $publisher = Publishing::firstOrCreate(
                    ['publishing' => $trimmedPublisher, 'publishing_low' => mb_strtolower($trimmedPublisher)]
                );
                $publication->update(['id_publishing' => $publisher->id_publishing]);
            }

            // Save genres to normalized tables
            foreach ($cleanedGenres as $genreName) {
                $trimmedGenre = trim($genreName);
                $genre = Genre::firstOrCreate(
                    ['slug' => Str::slug($trimmedGenre), 'name_en' => $trimmedGenre]
                );
                $publication->genres()->syncWithoutDetaching([$genre->id]);
            }

            // Sync categories
            $publication->categories()->sync($this->selectedCategories);

            // Sync publishers (new Publisher model)
            $publication->publishers()->sync($this->selectedPublishers);

            // Note: Cover image is auto-saved via updatedCoverImage() hook when user selects file
            // No need to save it again here

            // Update publication with new metadata
            $publication->update([
                'title' => $this->title,
                'title_low' => mb_strtolower($this->title),
                'issue_year' => $this->publicationYear ? (string) $this->publicationYear : null,
                'content_type_id' => $this->contentTypeId,
                'description' => $this->description ?: null,
            ]);

            // Update FileMetadata to confirmed state
            $this->fileMetadata->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'extracted_data' => [
                    'title' => [
                        'value' => $this->title,
                        'confidence' => 1.0,
                    ],
                    'authors' => array_map(
                        fn ($author) => [
                            'value' => trim($author),
                            'confidence' => 1.0,
                        ],
                        $cleanedAuthors
                    ),
                    'publication_year' => $this->publicationYear ? [
                        'value' => $this->publicationYear,
                        'confidence' => 1.0,
                    ] : null,
                    'publisher' => $this->publisher ? [
                        'value' => $this->publisher,
                        'confidence' => 1.0,
                    ] : null,
                    'genres' => array_map(
                        fn ($genre) => [
                            'value' => trim($genre),
                            'confidence' => 1.0,
                        ],
                        $cleanedGenres
                    ),
                    'theme' => $this->theme ? [
                        'value' => trim($this->theme),
                        'confidence' => 1.0,
                    ] : null,
                ],
                'extraction_method' => 'manual_entry',
            ]);

            DB::commit();

            Log::channel('folder_scan')->info('Metadata manually entered and saved to normalized tables', [
                'file_metadata_id' => $this->fileMetadata->id,
                'file_name' => $this->fileMetadata->file_name,
                'publication_id' => $publication->id_publication,
                'title' => $this->title,
                'authors_count' => count($cleanedAuthors),
                'genres_count' => count($cleanedGenres),
            ]);

            // Fire event to auto-apply metadata to Publication
            MetadataConfirmed::dispatch($this->fileMetadata);

            // Reset coverImage to clear the temporary file
            $this->coverImage = null;

            // Close modal and refresh parent queue
            $this->dispatch('refresh-metadata-queue')->to('admin.metadata-review-dashboard');
            $this->dispatch('notify', message: 'Metadata saved successfully!', type: 'success')->to('admin.metadata-review-dashboard');

            // Close the modal
            $this->dispatch('close-metadata-modal')->to('admin.metadata-review-dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save manual metadata entry', [
                'error' => $e->getMessage(),
                'file_metadata_id' => $this->fileMetadata->id ?? null,
            ]);
            $this->dispatch('notify', message: 'Failed to save metadata: '.$e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
        }
    }

    /**
     * Toggle extraction details visibility.
     */
    public function toggleDetails(): void
    {
        $this->showDetails = ! $this->showDetails;
    }

    /**
     * Reset form to empty state.
     */
    private function resetForm(): void
    {
        $this->title = '';
        $this->authors = [''];
        $this->publicationYear = null;
        $this->publisher = '';
        $this->genres = [''];
        $this->theme = '';
        $this->contentTypeId = null;
        $this->coverImage = null;
    }

    /**
     * Get confidence percentage for a field.
     */
    public function getConfidencePercent(string $field): int
    {
        $confidence = $this->confidenceScores[$field] ?? 0;

        return (int) ($confidence * 100);
    }

    /**
     * Get confidence badge color.
     */
    public function getConfidenceColor(float $confidence): string
    {
        if ($confidence >= 0.9) {
            return 'green';
        }
        if ($confidence >= 0.7) {
            return 'blue';
        }
        if ($confidence >= 0.5) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * Search authors by name (autocomplete).
     */
    public function searchAuthors(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Author::query()
            ->where(function ($q) use ($query) {
                $q->where('author', 'like', "%{$query}%")
                    ->orWhere('author_low', 'like', '%'.mb_strtolower($query).'%');
            })
            ->limit(10)
            ->get()
            ->map(fn ($author) => [
                'id' => $author->id_author,
                'name' => $author->author,
            ])
            ->toArray();
    }

    /**
     * Search publishers by name (autocomplete).
     */
    public function searchPublishers(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Publishing::query()
            ->where(function ($q) use ($query) {
                $q->where('publishing', 'like', "%{$query}%")
                    ->orWhere('publishing_low', 'like', '%'.mb_strtolower($query).'%');
            })
            ->limit(10)
            ->get()
            ->map(fn ($pub) => [
                'id' => $pub->id_publishing,
                'name' => $pub->publishing,
            ])
            ->toArray();
    }

    /**
     * Search genres by name (autocomplete, multilingual).
     */
    public function searchGenres(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Genre::query()
            ->where(function ($q) use ($query) {
                $q->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%")
                    ->orWhere('name_he', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name_en ?? $genre->name_ru ?? $genre->name_he ?? 'Unknown',
            ])
            ->toArray();
    }

    /**
     * Search themes by name (autocomplete).
     */
    public function searchThemes(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return \App\Models\Theme::query()
            ->where(function ($q) use ($query) {
                $q->where('theme', 'like', "%{$query}%")
                    ->orWhere('theme_low', 'like', '%'.mb_strtolower($query).'%');
            })
            ->limit(10)
            ->get()
            ->map(fn ($theme) => [
                'id' => $theme->id_theme,
                'name' => $theme->theme,
            ])
            ->toArray();
    }

    /**
     * Search categories by name (autocomplete, multilingual).
     */
    public function searchCategories(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Category::query()
            ->where(function ($q) use ($query) {
                $q->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%")
                    ->orWhere('name_he', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $this->selectedCategories)
            ->limit(10)
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->localizedName,
            ])
            ->toArray();
    }

    /**
     * Search publishers by name (autocomplete, multilingual) - New Publisher model.
     */
    public function searchNewPublishers(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Publisher::query()
            ->where(function ($q) use ($query) {
                $q->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%")
                    ->orWhere('name_he', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $this->selectedPublishers)
            ->limit(10)
            ->get()
            ->map(fn ($pub) => [
                'id' => $pub->id,
                'name' => $pub->localizedName,
            ])
            ->toArray();
    }

    /**
     * Add a category to the selected list.
     */
    public function addCategory(int $categoryId): void
    {
        if (! in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories[] = $categoryId;
        }
        $this->categorySearchQuery = '';
    }

    /**
     * Remove a category from the selected list.
     */
    public function removeCategory(int $categoryId): void
    {
        $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$categoryId]));
    }

    /**
     * Add a publisher to the selected list.
     */
    public function addPublisher(int $publisherId): void
    {
        if (! in_array($publisherId, $this->selectedPublishers)) {
            $this->selectedPublishers[] = $publisherId;
        }
        $this->publisherSearchQuery = '';
    }

    /**
     * Remove a publisher from the selected list.
     */
    public function removePublisher(int $publisherId): void
    {
        $this->selectedPublishers = array_values(array_diff($this->selectedPublishers, [$publisherId]));
    }

    /**
     * Get the currently selected categories with names.
     */
    public function getSelectedCategoriesWithNames(): array
    {
        return Category::whereIn('id', $this->selectedCategories)
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->localizedName,
            ])
            ->toArray();
    }

    /**
     * Get the currently selected publishers with names.
     */
    public function getSelectedPublishersWithNames(): array
    {
        return Publisher::whereIn('id', $this->selectedPublishers)
            ->get()
            ->map(fn ($pub) => [
                'id' => $pub->id,
                'name' => $pub->localizedName,
            ])
            ->toArray();
    }

    /**
     * Create new author (admin only).
     */
    public function createNewAuthors(string $name): array
    {
        abort_if(! auth()->user() || ! auth()->user()->role === 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $author = Author::firstOrCreate(
            ['author' => $trimmedName, 'author_low' => mb_strtolower($trimmedName)]
        );

        return [
            'id' => $author->id_author,
            'name' => $author->author,
        ];
    }

    /**
     * Create new publisher (admin only).
     */
    public function createNewPublishers(string $name): array
    {
        abort_if(! auth()->user() || ! auth()->user()->role === 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $publisher = Publishing::firstOrCreate(
            ['publishing' => $trimmedName],
            ['publishing_low' => mb_strtolower($trimmedName)]
        );

        return [
            'id' => $publisher->id_publishing,
            'name' => $publisher->publishing,
        ];
    }

    /**
     * Create new genre (admin only).
     */
    public function createNewGenres(string $name): array
    {
        abort_if(! auth()->user() || ! auth()->user()->role === 'admin', 403, 'Unauthorized');

        $genre = Genre::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name_en' => trim($name)]
        );

        return [
            'id' => $genre->id,
            'name' => $genre->name_en,
        ];
    }

    /**
     * Create new theme (admin only).
     */
    public function createNewThemes(string $name): array
    {
        abort_if(! auth()->user() || ! auth()->user()->role === 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $theme = \App\Models\Theme::firstOrCreate(
            ['theme' => $trimmedName, 'theme_low' => mb_strtolower($trimmedName)]
        );

        return [
            'id' => $theme->id_theme,
            'name' => $theme->theme,
        ];
    }

    /**
     * Create new category and add to selection (admin only).
     */
    public function createNewCategory(string $name): void
    {
        abort_if(! auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $category = Category::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($trimmedName)],
            ['name_en' => $trimmedName]
        );

        $this->addCategory($category->id);
    }

    /**
     * Create new publisher and add to selection (admin only).
     */
    public function createNewPublisher(string $name): void
    {
        abort_if(! auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $publisher = Publisher::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($trimmedName)],
            ['name_en' => $trimmedName]
        );

        $this->addPublisher($publisher->id);
    }

    public function render()
    {
        return view('livewire.admin.metadata-review-form');
    }
}
