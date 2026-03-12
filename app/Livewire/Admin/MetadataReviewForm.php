<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Events\MetadataConfirmed;
use App\Models\Author;
use App\Models\Section;
use App\Models\CustomField;
use App\Models\File;
use App\Models\FileMetadata;
use App\Models\Genre;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Publishing;
use App\Services\MetadataExtractors\DocumentTextExtractor;
use App\Services\MetadataExtractors\GeminiMetadataExtractorService;
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

    public array $authors = [];

    public ?int $publicationYear = null;

    public string $publisher = '';

    public string $issuer = '';

    public array $genres = [];

    public array $themes = [];

    public ?int $contentTypeId = null;

    public string $description = '';

    public $coverImage = null;

    public bool $useExtracted = false;

    public bool $useManual = false;

    // AI Suggestions text (for display badges)
    public array $aiSuggestions = [];

    public ?string $extractionStatus = null;

    public ?string $extractionMethod = null;

    public ?string $errorMessage = null;

    public bool $showDetails = false;

    public bool $showFilePreview = false;

    public array $customFieldValues = [];

    public $customFields = [];

    // Sections and Publishers (multi-select)
    public array $selectedSections = [];

    public array $selectedPublishers = [];

    public string $sectionSearchQuery = '';

    public string $publisherSearchQuery = '';

    public bool $geminiConfigured = false;

    public bool $isExtractingWithAI = false;

    // Track which new entries should be created on save
    public array $createNewAuthors = [];

    public array $createNewGenres = [];

    public bool $createNewPublisher = false;

    /**
     * Updated hook: Auto-save cover image when uploaded
     * This ensures the image is persisted even if user closes modal without clicking save button
     */
    public function updatedCoverImage(): void
    {
        if ($this->coverImage && $this->fileMetadata) {
            try {
                // Validate before saving
                $this->validate(['coverImage' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

                // Get publication
                $publication = Publication::with('files')->find((int) strtok($this->fileMetadata->file_id, "-"));
                if (!$publication) {
                    throw new \Exception('Publication not found');
                }

                // Generate unique filename
                $originalName = $this->coverImage->getClientOriginalName();
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '_' . time() . '.' . $extension;

                // Store the file
                $filePath = $this->coverImage->storeAs('covers', $uniqueName, 'public');

                // Delete existing cover image(s) for this publication (including soft-deleted)
                $existingCovers = File::withTrashed()
                    ->where('id_publication', $publication->id_publication)
                    ->where('file_type', 'cover')
                    ->get();

                Log::info('Cover upload: found existing covers', ['count' => $existingCovers->count()]);

                foreach ($existingCovers as $existingCover) {
                    // Delete the file from storage
                    if ($existingCover->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($existingCover->file_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existingCover->file_path);
                    }
                    // Force delete the database record using query builder (composite key + SoftDeletes workaround)
                    File::withTrashed()
                        ->where('id_publication', $existingCover->id_publication)
                        ->where('file_name', $existingCover->file_name)
                        ->forceDelete();
                }

                // Get next ord_num for this publication
                $nextOrdNum = File::where('id_publication', $publication->id_publication)
                    ->max('ord_num') + 1;

                Log::info('Cover upload: creating new cover', [
                    'publication_id' => $publication->id_publication,
                    'file_name' => $originalName,
                    'file_path' => $filePath,
                ]);

                // Create File record for cover image
                $newFile = File::create([
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

                Log::info('Cover upload: created new cover record', ['file_name' => $newFile->file_name ?? 'unknown']);

                // Show success notification
                $this->dispatch('notify', message: 'Cover image saved successfully!', type: 'success')->to('admin.metadata-review-dashboard');
            } catch (\Exception $e) {
                Log::error('Failed to auto-save cover image', ['error' => $e->getMessage()]);
                $this->dispatch('notify', message: 'Failed to save cover image: ' . $e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
            }
        }
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'authors' => 'array|min:1',
        'authors.*.value' => 'string|max:255',
        'publicationYear' => 'nullable|integer|min:1000|max:2100',
        'publisher' => 'nullable|string|max:255',
        'issuer' => 'nullable|string|max:255',
        'genres' => 'array',
        'genres.*.value' => 'string|max:255',
        'themes' => 'array',
        'themes.*.value' => 'string|max:255',
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
        $this->geminiConfigured = !empty(config('services.gemini.api_key'));

        // Initialize arrays with one empty item if empty
        if (empty($this->authors))
            $this->addAuthor();
        if (empty($this->genres))
            $this->addGenre();
        if (empty($this->themes))
            $this->addTheme();

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

        // Load extracted data
        if ($this->fileMetadata->status === 'processed' || $this->fileMetadata->status === 'confirmed' || $this->fileMetadata->status === 'rejected') {
            $this->title = $this->fileMetadata->getTitle() ?? '';

            $authors = $this->fileMetadata->getAuthors();
            if (!empty($authors)) {
                $this->authors = [];
                foreach ($authors as $author) {
                    $this->authors[] = ['id' => uniqid(), 'value' => $author];
                }
            }

            $this->publicationYear = $this->fileMetadata->getPublicationYear();
            $this->publisher = $this->fileMetadata->getPublisher() ?? '';
            $this->issuer = $this->fileMetadata->getIssuer() ?? '';

            $genres = $this->fileMetadata->getGenres();
            if (!empty($genres)) {
                $this->genres = [];
                foreach ($genres as $genre) {
                    $this->genres[] = ['id' => uniqid(), 'value' => $genre];
                }
            }

            $themes = $this->fileMetadata->getThemes();
            if (!empty($themes)) {
                $this->themes = [];
                foreach ($themes as $theme) {
                    $this->themes[] = ['id' => uniqid(), 'value' => $theme];
                }
            } else {
                // Legacy support check
                $legacyTheme = $this->fileMetadata->extracted_data['theme']['value'] ?? null;
                if ($legacyTheme) {
                    $this->themes = [['id' => uniqid(), 'value' => $legacyTheme]];
                }
            }

            $this->useExtracted = true;
        }

        // Load description from publication if confirmed
        if ($this->fileMetadata->status === 'confirmed') {
            // Use with('files') to eager-load files relationship including cover images
            $publication = Publication::with('files')->find((int) strtok($this->fileMetadata->file_id, "-"));
            if ($publication) {
                $this->description = $publication->description ?? '';
                $this->contentTypeId = $publication->content_type_id;
            }
        }

        // Load existing sections and publishers for the publication
        $this->loadSectionsAndPublishers();

        // Load custom fields if content type is selected
        $this->loadCustomFields();
    }

    /**
     * Load custom fields based on selected content type.
     */
    public function loadCustomFields(): void
    {
        if (!$this->contentTypeId) {
            $this->customFields = [];

            return;
        }

        $this->customFields = CustomField::where('content_type_id', $this->contentTypeId)
            ->orderedBySortOrder()
            ->get();

        // Load existing values if publication exists
        if ($this->fileMetadata && $this->fileMetadata->file_id) {
            $publication = Publication::find((int) strtok($this->fileMetadata->file_id, "-"));
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
     * Load existing sections and publishers for the publication.
     */
    private function loadSectionsAndPublishers(): void
    {
        if (!$this->fileMetadata || !$this->fileMetadata->file_id) {
            return;
        }

        $publication = Publication::with(['sections', 'publishers'])->find((int) strtok($this->fileMetadata->file_id, "-"));
        if (!$publication) {
            return;
        }

        // Load existing section IDs
        $this->selectedSections = $publication->sections->pluck('id')->toArray();

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
        if (!$this->fileMetadata) {
            return null;
        }

        $publication = Publication::with('files')->find((int) strtok($this->fileMetadata->file_id, "-"));
        if (!$publication) {
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
     * Extract metadata using Gemini AI and populate form fields.
     */
    public function extractWithAI(): void
    {
        if (!$this->geminiConfigured) {
            $this->dispatch('notify', message: __('Gemini API not configured'), type: 'error');

            return;
        }

        if (!$this->fileMetadata) {
            $this->dispatch('notify', message: __('No file metadata available'), type: 'error');

            return;
        }

        $this->isExtractingWithAI = true;

        try {
            // Extract publication ID from file_id format: "123-filename.pdf"
            $parts = explode('-', $this->fileMetadata->file_id, 2);
            $publicationId = (int) ($parts[0] ?? 0);

            if ($publicationId === 0) {
                throw new \Exception('Invalid publication ID');
            }

            // Get file path from file_registration_logs
            $fileLog = DB::table('file_registration_logs')
                ->where('publication_id', $publicationId)
                ->where('file_path', 'like', '%' . addcslashes($this->fileMetadata->file_name, '%_') . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$fileLog || !$fileLog->file_path) {
                throw new \Exception('File path not found');
            }

            // Build absolute file path
            $fullPath = $fileLog->file_path;
            if (preg_match('/^(\/|[A-Za-z]:)/', $fullPath)) {
                // Already an absolute path (Unix or Windows)
                $filePath = $fullPath;
            } else {
                $filePath = storage_path('app/content/' . $fullPath);
                if (!file_exists($filePath)) {
                    $filePath = storage_path('app/' . $fullPath);
                }
            }

            if (!file_exists($filePath)) {
                throw new \Exception('File not found: ' . $filePath);
            }

            // Extract text from document
            $textExtractor = app(DocumentTextExtractor::class);
            $maxChars = config('services.gemini.max_chars', 5000);

            try {
                $text = $textExtractor->extractText($filePath, $maxChars);
            } catch (\RuntimeException $e) {
                // User-friendly error (e.g., image-based PDF)
                throw $e;
            }

            if (empty($text)) {
                throw new \Exception('Could not extract text from document');
            }

            // Call Gemini API
            $geminiService = app(GeminiMetadataExtractorService::class);
            $extracted = $geminiService->extract($text);

            // Populate form fields with extracted data
            if ($extracted->getTitle()) {
                $this->title = $extracted->getTitle();
            }

            $authors = $extracted->getAuthors();
            if (!empty($authors)) {
                $this->authors = [];
                $this->createNewAuthors = [];
                foreach ($authors as $author) {
                    $this->authors[] = ['id' => uniqid(), 'value' => $author];
                    // Only flag for creation if author doesn't exist
                    $exists = Author::where('author_low', mb_strtolower(trim($author)))->exists();
                    $this->createNewAuthors[] = !$exists;
                }
            }

            if ($extracted->getPublicationYear()) {
                $this->publicationYear = $extracted->getPublicationYear();
            }

            if ($extracted->getPublisher()) {
                $this->publisher = $extracted->getPublisher();
                $this->aiSuggestions['publisher'] = $extracted->getPublisher();
                // Only flag for creation if publisher doesn't exist
                $exists = Publishing::where('publishing_low', mb_strtolower(trim($extracted->getPublisher())))->exists();
                $this->createNewPublisher = !$exists;
            }

            if ($extracted->getIssuer()) {
                $this->issuer = $extracted->getIssuer();
                $this->aiSuggestions['issuer'] = $extracted->getIssuer();
            }

            $genres = $extracted->getGenres();
            if (!empty($genres)) {
                $this->genres = [];
                $this->createNewGenres = [];
                foreach ($genres as $genre) {
                    $this->genres[] = ['id' => uniqid(), 'value' => $genre];
                    // Only flag for creation if genre doesn't exist
                    $exists = Genre::where('slug', Str::slug($genre))->exists();
                    $this->createNewGenres[] = !$exists;
                }
            }

            $themes = $extracted->getThemes();
            if (!empty($themes)) {
                $this->themes = [];
                foreach ($themes as $theme) {
                    $this->themes[] = ['id' => uniqid(), 'value' => $theme];
                }
            }

            // Resolve and set Content Type
            if ($extracted->getContentType()) {
                $this->aiSuggestions['content_type'] = $extracted->getContentType();
                $this->contentTypeId = $this->resolveContentTypeId($extracted->getContentType());
            }

            // Resolve and set Section
            if ($extracted->getSection()) {
                $this->aiSuggestions['section'] = $extracted->getSection();
                $sectionId = $this->resolveSectionId($extracted->getSection());
                if ($sectionId) {
                    $this->selectedSections = [$sectionId];
                }
            }

            if ($extracted->getDescription()) {
                $this->description = $extracted->getDescription();
            }

            // Update status and mark as extracted
            $this->useExtracted = true;
            $this->extractionStatus = 'processed';
            $this->extractionMethod = 'gemini_llm';

            Log::channel('folder_scan')->info('AI extraction completed in form', [
                'file_metadata_id' => $this->fileMetadata->id,
                'title' => $this->title,
                'authors_count' => count($this->authors),
                'genres_count' => count($this->genres),
            ]);

            $this->dispatch('notify', message: __('AI extraction successful'), type: 'success');

        } catch (\Exception $e) {
            Log::channel('folder_scan')->error('AI extraction failed in form', [
                'file_metadata_id' => $this->fileMetadata->id ?? null,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', message: __('AI extraction failed') . ': ' . $e->getMessage(), type: 'error');
        } finally {
            $this->isExtractingWithAI = false;
        }
    }

    private function resolveContentTypeId(string $aiValue): ?int
    {
        // 1. Try exact match by ID if integer (unlikely from AI but possible)
        if (is_numeric($aiValue)) {
            return (int) $aiValue;
        }

        $aiValueLower = mb_strtolower(trim($aiValue));

        // 2. Try exact match name_ru, name_en, slug
        $type = DB::table('content_types')
            ->where(DB::raw('LOWER(name_ru)'), $aiValueLower)
            ->orWhere(DB::raw('LOWER(name_en)'), $aiValueLower)
            ->orWhere('slug', Str::slug($aiValue))
            ->first();

        if ($type)
            return $type->id;

        // 3. Simple mapping for known variations
        $map = [
            'книга' => 'books',
            'книги' => 'books',
            'book' => 'books',
            'журнал' => 'magazines',
            'журналы' => 'magazines',
            'magazine' => 'magazines',
            'статья' => 'articles',
            'статьи' => 'articles',
            'article' => 'articles',
        ];

        if (isset($map[$aiValueLower])) {
            $slug = $map[$aiValueLower];
            $type = DB::table('content_types')->where('slug', $slug)->first();
            if ($type)
                return $type->id;
        }

        return null;
    }

    /**
     * Resolve Section ID from AI value with fuzzy matching.
     */
    private function resolveSectionId(string $aiValue): ?int
    {
        if (is_numeric($aiValue)) {
            return (int) $aiValue;
        }

        $aiValueLower = mb_strtolower(trim($aiValue));

        // 1. Exact match (case-insensitive)
        $section = Section::where(DB::raw('LOWER(name_ru)'), $aiValueLower)
            ->orWhere(DB::raw('LOWER(name_en)'), $aiValueLower)
            ->orWhere('slug', Str::slug($aiValue))
            ->first();

        if ($section)
            return $section->id;

        // 2. Fuzzy match - try to find if the AI value matches loosely
        // e.g. "Biology" matching "Science / Biology"
        $section = Section::where(DB::raw('LOWER(name_ru)'), 'like', "%{$aiValueLower}%")
            ->orWhere(DB::raw('LOWER(name_en)'), 'like', "%{$aiValueLower}%")
            ->first();

        if ($section)
            return $section->id;

        return null;
    }

    /**
     * Add a new author field.
     */
    public function addAuthor(): void
    {
        $this->authors[] = ['id' => uniqid(), 'value' => ''];
        $this->createNewAuthors[] = false;
    }

    /**
     * Remove an author field.
     */
    public function removeAuthor(int $index): void
    {
        unset($this->authors[$index]);
        unset($this->createNewAuthors[$index]);
        $this->authors = array_values($this->authors);
        $this->createNewAuthors = array_values($this->createNewAuthors);
    }

    /**
     * Add a new genre field.
     */
    public function addGenre(): void
    {
        $this->genres[] = ['id' => uniqid(), 'value' => ''];
        $this->createNewGenres[] = false;
    }

    /**
     * Remove a genre field.
     */
    public function removeGenre(int $index): void
    {
        unset($this->genres[$index]);
        unset($this->createNewGenres[$index]);
        $this->genres = array_values($this->genres);
        $this->createNewGenres = array_values($this->createNewGenres);
    }

    /**
     * Add a new theme field.
     */
    public function addTheme(): void
    {
        $this->themes[] = ['id' => uniqid(), 'value' => ''];
    }

    /**
     * Remove a theme field.
     */
    public function removeTheme(int $index): void
    {
        unset($this->themes[$index]);
        $this->themes = array_values($this->themes);
    }

    /**
     * Confirm extraction with form data and save to normalized tables.
     */
    public function confirmExtraction(): void
    {
        // Debug: dispatch notification to confirm method was called
        $this->dispatch('notify', message: 'Processing confirmation...', type: 'info')->to('admin.metadata-review-dashboard');

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->flatten()->join(', ');
            $this->dispatch('notify', message: 'Validation failed: ' . $errors, type: 'error')->to('admin.metadata-review-dashboard');
            return;
        }

        DB::beginTransaction();
        try {
            // Clean empty values
            $cleanedAuthors = array_filter(
                array_column($this->authors, 'value'),
                fn($author) => !empty(trim($author))
            );
            $cleanedGenres = array_filter(
                array_column($this->genres, 'value'),
                fn($genre) => !empty(trim($genre))
            );
            $cleanedThemes = array_filter(
                array_column($this->themes, 'value'),
                fn($theme) => !empty(trim($theme))
            );

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find((int) strtok($this->fileMetadata->file_id, "-"));

            if (!$publication) {
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
            if (!empty($this->publisher)) {
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

            // Sync sections
            if (!empty($this->selectedSections)) {
                $publication->sections()->sync($this->selectedSections);
            }

            // Sync publishers (new Publisher model)
            if (!empty($this->selectedPublishers)) {
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
            $updateData = [
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'extracted_data' => [
                    'title' => [
                        'value' => $this->title,
                        'confidence' => 1.0,
                    ],
                    'authors' => array_map(
                        fn($author) => [
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
                    'issuer' => $this->issuer ? [
                        'value' => $this->issuer,
                        'confidence' => 1.0,
                    ] : null,
                    'genres' => array_map(
                        fn($genre) => [
                            'value' => trim($genre),
                            'confidence' => 1.0,
                        ],
                        $cleanedGenres
                    ),
                    'themes' => array_map(
                        fn($theme) => [
                            'value' => trim($theme),
                            'confidence' => 1.0,
                        ],
                        $cleanedThemes
                    ),
                    'content_type_id' => $this->contentTypeId ? [
                        'value' => $this->contentTypeId,
                        'confidence' => 1.0,
                    ] : null,
                    'section_ids' => !empty($this->selectedSections) ? [
                        'value' => $this->selectedSections,
                        'confidence' => 1.0,
                    ] : null,
                ],
            ];

            $this->fileMetadata->update($updateData);

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
            
            session()->flash('notify', [
                'message' => 'Metadata confirmed and saved successfully!',
                'type' => 'success'
            ]);
            $this->redirectRoute('dashboard', navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm metadata', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_metadata_id' => $this->fileMetadata->id ?? null,
            ]);
            \Log::channel('folder_scan')->error('Metadata confirmation failed: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Failed to confirm metadata: ' . $e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
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
     * Update metadata for rejected publications without marking as confirmed.
     * This allows editing rejected items and saving changes.
     */
    public function updateMetadata(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $cleanedAuthors = array_filter(
                array_column($this->authors, 'value'),
                fn($author) => !empty(trim($author))
            );
            $cleanedGenres = array_filter(
                array_column($this->genres, 'value'),
                fn($genre) => !empty(trim($genre))
            );
            $cleanedThemes = array_filter(
                array_column($this->themes, 'value'),
                fn($theme) => !empty(trim($theme))
            );

            // Get or create Publication
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find((int) strtok($this->fileMetadata->file_id, "-"));

            if (!$publication) {
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
            if (!empty($this->publisher)) {
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

            // Sync sections
            $publication->sections()->sync($this->selectedSections);

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
                    'title' => [
                        'value' => $this->title,
                        'confidence' => 1.0,
                    ],
                    'authors' => array_map(
                        fn($author) => [
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
                    'issuer' => $this->issuer ? [
                        'value' => $this->issuer,
                        'confidence' => 1.0,
                    ] : null,
                    'genres' => array_map(
                        fn($genre) => [
                            'value' => trim($genre),
                            'confidence' => 1.0,
                        ],
                        $cleanedGenres
                    ),
                    'themes' => array_map(
                        fn($theme) => [
                            'value' => trim($theme),
                            'confidence' => 1.0,
                        ],
                        $cleanedThemes
                    ),
                    'content_type_id' => $this->contentTypeId ? [
                        'value' => $this->contentTypeId,
                        'confidence' => 1.0,
                    ] : null,
                    'section_ids' => !empty($this->selectedSections) ? [
                        'value' => $this->selectedSections,
                        'confidence' => 1.0,
                    ] : null,
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
            $this->dispatch('notify', message: 'Failed to update metadata: ' . $e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
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
            $cleanedAuthors = array_filter(
                array_column($this->authors, 'value'),
                fn($author) => !empty(trim($author))
            );
            $cleanedGenres = array_filter(
                array_column($this->genres, 'value'),
                fn($genre) => !empty(trim($genre))
            );

            // Get or create Publication (from FileMetadata's relationship)
            $publication = $this->fileMetadata->file()->first()?->publication
                ?? Publication::find((int) strtok($this->fileMetadata->file_id, "-"));

            if (!$publication) {
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
            if (!empty($this->publisher)) {
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

            // Sync sections
            $publication->sections()->sync($this->selectedSections);

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
                        fn($author) => [
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
                        fn($genre) => [
                            'value' => trim($genre),
                            'confidence' => 1.0,
                        ],
                        $cleanedGenres
                    ),
                    'themes' => array_map(
                        fn($theme) => [
                            'value' => trim($theme),
                            'confidence' => 1.0,
                        ],
                        $cleanedThemes
                    ),
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
            $this->dispatch('notify', message: 'Failed to save metadata: ' . $e->getMessage(), type: 'error')->to('admin.metadata-review-dashboard');
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
                    ->orWhere('author_low', 'like', '%' . mb_strtolower($query) . '%');
            })
            ->limit(10)
            ->get()
            ->map(fn($author) => [
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
                    ->orWhere('publishing_low', 'like', '%' . mb_strtolower($query) . '%');
            })
            ->limit(10)
            ->get()
            ->map(fn($pub) => [
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
            ->map(fn($genre) => [
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
                    ->orWhere('theme_low', 'like', '%' . mb_strtolower($query) . '%');
            })
            ->limit(10)
            ->get()
            ->map(fn($theme) => [
                'id' => $theme->id_theme,
                'name' => $theme->theme,
            ])
            ->toArray();
    }

    /**
     * Search sections by name (autocomplete, multilingual).
     */
    public function searchSections(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Section::query()
            ->where(function ($q) use ($query) {
                $q->where('name_en', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%")
                    ->orWhere('name_he', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $this->selectedSections)
            ->limit(10)
            ->get()
            ->map(fn($cat) => [
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
            ->map(fn($pub) => [
                'id' => $pub->id,
                'name' => $pub->localizedName,
            ])
            ->toArray();
    }

    /**
     * Add a section to the selected list.
     */
    public function addSection(int $sectionId): void
    {
        if (!in_array($sectionId, $this->selectedSections)) {
            $this->selectedSections[] = $sectionId;
        }
        $this->sectionSearchQuery = '';
    }

    /**
     * Remove a section from the selected list.
     */
    public function removeSection(int $sectionId): void
    {
        $this->selectedSections = array_values(array_diff($this->selectedSections, [$sectionId]));
    }

    /**
     * Add a publisher to the selected list.
     */
    public function addPublisher(int $publisherId): void
    {
        if (!in_array($publisherId, $this->selectedPublishers)) {
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
     * Get the currently selected sections with names.
     */
    public function getSelectedSectionsWithNames(): array
    {
        return Section::whereIn('id', $this->selectedSections)
            ->get()
            ->map(fn($cat) => [
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
            ->map(fn($pub) => [
                'id' => $pub->id,
                'name' => $pub->localizedName,
            ])
            ->toArray();
    }

    /**
     * Create new author (admin only).
     */
    /**
     * Create new author (admin only).
     */
    public function storeAuthor(string $name): array
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

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
    public function storePublisher(string $name): array
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

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
    public function storeGenre(string $name): array
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

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
    public function storeTheme(string $name): array
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

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
     * Create new section and add to selection (admin only).
     */
    public function createNewSection(string $name): void
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

        $trimmedName = trim($name);
        $section = Section::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($trimmedName)],
            ['name_en' => $trimmedName]
        );

        $this->addSection($section->id);
    }

    /**
     * Create new publisher and add to selection (admin only).
     */
    public function createNewPublisher(string $name): void
    {
        abort_if(!auth()->user() || auth()->user()->role !== 'admin', 403, 'Unauthorized');

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

