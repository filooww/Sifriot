<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\ContentType;
use App\Models\CustomField;
use App\Models\File;
use App\Models\FileRegistrationLog;
use App\Models\Publication;
use App\Services\FileStorageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileRegistrationForm extends Component
{
    use WithFileUploads;

    public string $registrationMode = 'register_existing';

    public ?string $selectedFilePath = null;

    public $uploadedFile;

    public string $publicationTitle = '';

    public ?int $contentTypeId = null;

    public string $status = 'pending';

    public array $customFieldValues = [];

    public array $customFields = [];

    protected FileStorageService $fileStorage;

    public function boot(FileStorageService $fileStorage): void
    {
        $this->fileStorage = $fileStorage;
    }

    public function mount(?string $filePath = null): void
    {
        if ($filePath) {
            $this->registrationMode = 'register_existing';
            $this->selectedFilePath = $filePath;
            $this->loadFileMetadata();
        }
    }

    protected function loadFileMetadata(): void
    {
        if (! $this->selectedFilePath) {
            return;
        }

        try {
            $metadata = $this->fileStorage->getFileMetadata($this->selectedFilePath);
            $this->publicationTitle = $metadata['suggested_title'];
            $this->contentTypeId = $metadata['content_type_id'];
        } catch (\Exception $e) {
            session()->flash('error', __('Unable to load file metadata.'));
        }
    }

    public function updatedContentTypeId(?int $value): void
    {
        if (! $value) {
            $this->customFields = [];
            $this->customFieldValues = [];

            return;
        }

        $this->loadCustomFields();
    }

    protected function loadCustomFields(): void
    {
        if (! $this->contentTypeId) {
            $this->customFields = [];

            return;
        }

        $this->customFields = CustomField::where('content_type_id', $this->contentTypeId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        // Initialize custom field values array
        foreach ($this->customFields as $field) {
            if (! isset($this->customFieldValues[$field['field_name']])) {
                $this->customFieldValues[$field['field_name']] = null;
            }
        }
    }

    public function updatedUploadedFile(): void
    {
        if (! $this->uploadedFile) {
            return;
        }

        // Validate MIME type
        $allowedMimeTypes = config('library.upload.allowed_mime_types');
        $mimeType = $this->uploadedFile->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes)) {
            $this->reset('uploadedFile');
            session()->flash('error', __('Invalid file type'));

            return;
        }

        // Validate file size (500MB max)
        $maxSize = config('library.upload.max_file_size');
        if ($this->uploadedFile->getSize() > $maxSize) {
            $this->reset('uploadedFile');
            session()->flash('error', __('File too large'));

            return;
        }
    }

    protected function validateCustomFields(): void
    {
        $rules = [];

        foreach ($this->customFields as $field) {
            $fieldRules = [];

            if ($field['is_required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add type-specific validation
            switch ($field['field_type']) {
                case 'text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
                case 'long_text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:10000';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
                case 'dropdown':
                    if (isset($field['field_config']['options'])) {
                        $validOptions = array_column($field['field_config']['options'], 'value');
                        $fieldRules[] = 'in:'.implode(',', $validOptions);
                    }
                    break;
                case 'multiselect':
                    $fieldRules[] = 'array';
                    break;
            }

            $rules["customFieldValues.{$field['field_name']}"] = implode('|', $fieldRules);
        }

        if (! empty($rules)) {
            $this->validate($rules);
        }
    }

    protected function saveCustomFieldValues(Publication $publication): void
    {
        foreach ($this->customFields as $field) {
            $value = $this->customFieldValues[$field['field_name']] ?? null;

            if ($value === null && ! $field['is_required']) {
                continue;
            }

            $publication->setCustomFieldValue($field['field_name'], $value);
        }
    }

    public function registerFile(): void
    {
        $this->validate([
            'selectedFilePath' => 'required|string',
            'publicationTitle' => 'required|max:500',
            'contentTypeId' => 'required|exists:content_types,id',
        ]);

        // Validate custom fields
        $this->validateCustomFields();

        try {
            // Check duplicate file path
            $fullPath = Storage::disk('local')->path($this->selectedFilePath);
            if (FileRegistrationLog::where('file_path', $fullPath)->exists()) {
                session()->flash('error', __('This file is already registered'));

                return;
            }

            // Create publication record with pending status
            $publication = Publication::create([
                'title' => $this->publicationTitle,
                'title_low' => strtolower($this->publicationTitle),
                'content_type_id' => $this->contentTypeId,
                'status' => 'pending',
                'upload_date' => now(),
            ]);

            // Save custom field values
            $this->saveCustomFieldValues($publication);

            // Create file record (only path, not contents) - use relative path for file_source
            File::create([
                'id_publication' => $publication->id_publication,
                'file_name' => basename($this->selectedFilePath),
                'file_source' => $this->selectedFilePath,  // Relative path
                'mime_type' => Storage::disk('local')->mimeType($this->selectedFilePath),
                'file_size_bytes' => Storage::disk('local')->size($this->selectedFilePath),
                'ord_num' => 1,
            ]);

            // Create registration log
            FileRegistrationLog::create([
                'publication_id' => $publication->id_publication,
                'file_path' => $fullPath,
                'registration_source' => 'manual_registration',
                'status' => 'processed',
                'registered_by' => auth()->id(),
            ]);

            // Dispatch metadata extraction job if enabled
            if (config('library.extraction.enabled', true)) {
                $fileId = "{$publication->id_publication}-".basename($this->selectedFilePath);
                ExtractMetadataFromFile::dispatch(
                    $fileId,
                    $fullPath,
                    $this->contentTypeId,
                    Storage::disk('local')->mimeType($this->selectedFilePath)
                );
            }

            session()->flash('message', __('File registered successfully. Metadata extraction started...'));
            $this->dispatch('file-registered-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'selectedFilePath', 'customFields', 'customFieldValues']);

        } catch (\Exception $e) {
            Log::error('File registration failed', [
                'error' => $e->getMessage(),
                'file_path' => $this->selectedFilePath,
            ]);
            session()->flash('error', __('Unable to save file. Check server permissions.'));
        }
    }

    public function uploadFile(): void
    {
        $this->validate([
            'publicationTitle' => 'required|max:500',
            'contentTypeId' => 'required|exists:content_types,id',
            'uploadedFile' => 'required|file',
        ]);

        // Validate custom fields
        $this->validateCustomFields();

        try {
            $contentType = ContentType::find($this->contentTypeId);
            $extension = $this->uploadedFile->getClientOriginalExtension();
            $originalName = $this->uploadedFile->getClientOriginalName();

            // Sanitize the filename - remove path info and normalize
            $sanitizedName = preg_replace('/[^\p{L}\p{N}\s\.\-_]/u', '', pathinfo($originalName, PATHINFO_FILENAME));
            $sanitizedName = trim($sanitizedName) ?: 'file';
            $filename = $sanitizedName.'.'.$extension;

            // Determine storage path based on content type
            $storagePath = 'content/'.$contentType->folder_name;

            // Check if file already exists and make unique if necessary
            $counter = 1;
            $finalFilename = $filename;
            while (Storage::disk('local')->exists($storagePath.'/'.$finalFilename)) {
                $finalFilename = $sanitizedName.'_'.$counter.'.'.$extension;
                $counter++;
            }

            // Store uploaded file
            $filePath = $this->uploadedFile->storeAs($storagePath, $finalFilename, 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            // Create publication record
            $publication = Publication::create([
                'title' => $this->publicationTitle,
                'title_low' => strtolower($this->publicationTitle),
                'content_type_id' => $this->contentTypeId,
                'status' => 'pending',
                'upload_date' => now(),
            ]);

            // Save custom field values
            $this->saveCustomFieldValues($publication);

            // Create file record - use relative path for file_source
            File::create([
                'id_publication' => $publication->id_publication,
                'file_name' => $finalFilename,
                'file_source' => $filePath,  // Relative path, e.g., 'content/books/filename.pdf'
                'mime_type' => $this->uploadedFile->getMimeType(),
                'file_size_bytes' => $this->uploadedFile->getSize(),
                'ord_num' => 1,
            ]);

            // Create registration log - use absolute path
            FileRegistrationLog::create([
                'publication_id' => $publication->id_publication,
                'file_path' => $fullPath,
                'registration_source' => 'admin_upload',
                'status' => 'processed',
                'registered_by' => auth()->id(),
            ]);

            // Dispatch metadata extraction job if enabled
            if (config('library.extraction.enabled', true)) {
                $fileId = "{$publication->id_publication}-{$finalFilename}";
                ExtractMetadataFromFile::dispatch(
                    $fileId,
                    $fullPath,
                    $this->contentTypeId,
                    $this->uploadedFile->getMimeType()
                );
            }

            session()->flash('message', __('File uploaded successfully. Metadata extraction started...'));
            $this->dispatch('file-uploaded-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'uploadedFile', 'customFields', 'customFieldValues']);

        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', __('Unable to save file. Check server permissions.'));
        }
    }

    public function render()
    {
        return view('livewire.admin.file-registration-form', [
            'contentTypes' => ContentType::all(),
        ])->layout('layouts.app');
    }
}
