<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Events\MetadataConfirmed;
use App\Models\Author;
use App\Models\ContentType;
use App\Models\File;
use App\Models\FileMetadata;
use App\Models\Genre;
use App\Models\Publication;
use App\Models\Publishing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public string $isbn = '';

    public string $doi = '';

    public array $genres = [''];

    public string $theme = '';

    public ?int $contentTypeId = null;

    public $coverImage = null;

    public bool $useExtracted = false;

    public bool $useManual = false;

    public array $confidenceScores = [];

    public ?string $extractionStatus = null;

    public ?string $extractionMethod = null;

    public ?string $errorMessage = null;

    public bool $showDetails = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'authors' => 'array|min:1',
        'authors.*' => 'string|max:255',
        'publicationYear' => 'nullable|integer|min:1000|max:2100',
        'publisher' => 'nullable|string|max:255',
        'isbn' => 'nullable|string|regex:/^(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]$/',
        'doi' => 'nullable|string|regex:/^10\.\d{4,}\/\S+$/',
        'genres' => 'array',
        'genres.*' => 'string|max:255',
        'theme' => 'nullable|string|max:255',
        'contentTypeId' => 'nullable|integer|exists:content_types,id',
        'coverImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
    ];

    /**
     * Mount the component with FileMetadata.
     *
     * @param FileMetadata $fileMetadata
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
        if (!$this->fileMetadata) {
            return;
        }

        $this->extractionStatus = $this->fileMetadata->status;
        $this->extractionMethod = $this->fileMetadata->extraction_method;
        $this->errorMessage = $this->fileMetadata->error_message;
        $this->confidenceScores = $this->fileMetadata->confidence_scores ?? [];

        // Load extracted data
        if ($this->fileMetadata->status === 'processed' || $this->fileMetadata->status === 'confirmed') {
            $this->title = $this->fileMetadata->getTitle() ?? '';
            $this->authors = !empty($this->fileMetadata->getAuthors())
                ? $this->fileMetadata->getAuthors()
                : [''];
            $this->publicationYear = $this->fileMetadata->getPublicationYear();
            $this->publisher = $this->fileMetadata->getPublisher() ?? '';
            $this->isbn = $this->fileMetadata->getIsbn() ?? '';
            $this->doi = $this->fileMetadata->getDoi() ?? '';
            $this->genres = !empty($this->fileMetadata->getGenres())
                ? $this->fileMetadata->getGenres()
                : [''];
            $this->useExtracted = true;
        }
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
     *
     * @param int $index
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
     *
     * @param int $index
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
            $cleanedAuthors = array_filter($this->authors, fn ($author) => !empty(trim($author)));
            $cleanedGenres = array_filter($this->genres, fn ($genre) => !empty(trim($genre)));

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find($this->fileMetadata->file_id);

            if (!$publication) {
                throw new \Exception('Publication not found for this file metadata');
            }

            // Save authors to normalized tables
            foreach ($cleanedAuthors as $authorName) {
                $author = Author::firstOrCreate(
                    ['name' => trim($authorName)]
                );
                $publication->authors()->syncWithoutDetaching([$author->id_author]);
            }

            // Save publisher if provided
            if (!empty($this->publisher)) {
                $publisher = Publishing::firstOrCreate(
                    ['publisher' => trim($this->publisher)]
                );
                $publication->update(['id_publishing' => $publisher->id_publishing]);
            }

            // Save genres to normalized tables
            foreach ($cleanedGenres as $genreName) {
                $genre = Genre::firstOrCreate(
                    ['slug' => Str::slug($genreName)],
                    ['name_en' => trim($genreName)]
                );
                $publication->genres()->syncWithoutDetaching([$genre->id]);
            }

            // Handle cover image upload
            if ($this->coverImage) {
                $coverPath = $this->coverImage->store(
                    'public/covers',
                    'public'
                );

                // Create File record for cover image
                File::create([
                    'id_publication' => $publication->id_publication,
                    'file_name' => $this->coverImage->getClientOriginalName(),
                    'file_name_low' => mb_strtolower($this->coverImage->getClientOriginalName()),
                    'file_size' => $this->coverImage->getSize(),
                    'file_size_bytes' => $this->coverImage->getSize(),
                    'mime_type' => $this->coverImage->getMimeType(),
                    'file_type' => 'cover',
                    'file_path' => $coverPath,
                    'file_source' => 'manual_upload',
                ]);
            }

            // Update publication with new metadata
            $publication->update([
                'title' => $this->title,
                'title_low' => mb_strtolower($this->title),
                'issue_year' => $this->publicationYear ? (string)$this->publicationYear : null,
                'content_type_id' => $this->contentTypeId,
            ]);

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
                    'isbn' => $this->isbn ? [
                        'value' => $this->isbn,
                        'confidence' => $this->confidenceScores['isbn'] ?? 0.8,
                    ] : null,
                    'doi' => $this->doi ? [
                        'value' => $this->doi,
                        'confidence' => $this->confidenceScores['doi'] ?? 0.8,
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

            // Close modal and refresh parent queue
            $this->dispatch('refresh-metadata-queue');
            $this->dispatch('notify', message: 'Metadata confirmed and saved successfully!', type: 'success');

            // Emit parent close event
            $this->parent?->call('set', 'selectedMetadataId', null);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm metadata', [
                'error' => $e->getMessage(),
                'file_metadata_id' => $this->fileMetadata->id ?? null,
            ]);
            $this->dispatch('notify', message: 'Failed to confirm metadata: ' . $e->getMessage(), type: 'error');
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
            $this->dispatch('notify', message: 'Failed to reject extraction: ' . $e->getMessage(), type: 'error');
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
            $cleanedAuthors = array_filter($this->authors, fn ($author) => !empty(trim($author)));
            $cleanedGenres = array_filter($this->genres, fn ($genre) => !empty(trim($genre)));

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find($this->fileMetadata->file_id);

            if (!$publication) {
                throw new \Exception('Publication not found for this file metadata');
            }

            // Save authors to normalized tables
            foreach ($cleanedAuthors as $authorName) {
                $author = Author::firstOrCreate(
                    ['name' => trim($authorName)]
                );
                $publication->authors()->syncWithoutDetaching([$author->id_author]);
            }

            // Save publisher if provided
            if (!empty($this->publisher)) {
                $publisher = Publishing::firstOrCreate(
                    ['publisher' => trim($this->publisher)]
                );
                $publication->update(['id_publishing' => $publisher->id_publishing]);
            }

            // Save genres to normalized tables
            foreach ($cleanedGenres as $genreName) {
                $genre = Genre::firstOrCreate(
                    ['slug' => Str::slug($genreName)],
                    ['name_en' => trim($genreName)]
                );
                $publication->genres()->syncWithoutDetaching([$genre->id]);
            }

            // Handle cover image upload
            if ($this->coverImage) {
                $coverPath = $this->coverImage->store(
                    'public/covers',
                    'public'
                );

                // Create File record for cover image
                File::create([
                    'id_publication' => $publication->id_publication,
                    'file_name' => $this->coverImage->getClientOriginalName(),
                    'file_name_low' => mb_strtolower($this->coverImage->getClientOriginalName()),
                    'file_size' => $this->coverImage->getSize(),
                    'file_size_bytes' => $this->coverImage->getSize(),
                    'mime_type' => $this->coverImage->getMimeType(),
                    'file_type' => 'cover',
                    'file_path' => $coverPath,
                    'file_source' => 'manual_upload',
                ]);
            }

            // Update publication with new metadata
            $publication->update([
                'title' => $this->title,
                'title_low' => mb_strtolower($this->title),
                'issue_year' => $this->publicationYear ? (string)$this->publicationYear : null,
                'content_type_id' => $this->contentTypeId,
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
                    'isbn' => $this->isbn ? [
                        'value' => $this->isbn,
                        'confidence' => 1.0,
                    ] : null,
                    'doi' => $this->doi ? [
                        'value' => $this->doi,
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

            // Close modal and refresh parent queue
            $this->dispatch('refresh-metadata-queue');
            $this->dispatch('notify', message: 'Metadata saved successfully!', type: 'success');

            // Emit parent close event
            $this->parent?->call('set', 'selectedMetadataId', null);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save manual metadata entry', [
                'error' => $e->getMessage(),
                'file_metadata_id' => $this->fileMetadata->id ?? null,
            ]);
            $this->dispatch('notify', message: 'Failed to save metadata: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Toggle extraction details visibility.
     */
    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
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
        $this->isbn = '';
        $this->doi = '';
        $this->genres = [''];
        $this->theme = '';
        $this->contentTypeId = null;
        $this->coverImage = null;
    }

    /**
     * Get confidence percentage for a field.
     *
     * @param string $field
     * @return int
     */
    public function getConfidencePercent(string $field): int
    {
        $confidence = $this->confidenceScores[$field] ?? 0;
        return (int) ($confidence * 100);
    }

    /**
     * Get confidence badge color.
     *
     * @param float $confidence
     * @return string
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

    public function render()
    {
        return view('livewire.admin.metadata-review-form');
    }
}
