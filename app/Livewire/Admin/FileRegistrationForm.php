<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ContentType;
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

    public function registerFile(): void
    {
        $this->validate([
            'publicationTitle' => 'required|max:500',
            'contentTypeId' => 'required|exists:content_types,id',
        ]);

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

            // Create file record (only path, not contents)
            File::create([
                'id_publication' => $publication->id_publication,
                'file_name' => basename($this->selectedFilePath),
                'file_source' => $fullPath,
                'mime_type' => Storage::disk('local')->mimeType($this->selectedFilePath),
                'file_size_bytes' => Storage::disk('local')->size($this->selectedFilePath),
            ]);

            // Create registration log
            FileRegistrationLog::create([
                'publication_id' => $publication->id_publication,
                'file_path' => $fullPath,
                'registration_source' => 'manual_registration',
                'status' => 'processed',
                'registered_by' => auth()->id(),
            ]);

            session()->flash('message', __('File registered successfully'));
            $this->dispatch('file-registered-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'selectedFilePath']);

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

        try {
            $contentType = ContentType::find($this->contentTypeId);
            $extension = $this->uploadedFile->getClientOriginalExtension();
            $originalName = $this->uploadedFile->getClientOriginalName();

            // Generate unique filename
            $uniqueFilename = hash('sha256', $originalName.time()).'.'.$extension;

            // Determine storage path based on content type
            $storagePath = 'content/'.$contentType->folder_name;

            // Store uploaded file
            $filePath = $this->uploadedFile->storeAs($storagePath, $uniqueFilename, 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            // Create publication record
            $publication = Publication::create([
                'title' => $this->publicationTitle,
                'title_low' => strtolower($this->publicationTitle),
                'content_type_id' => $this->contentTypeId,
                'status' => 'pending',
                'upload_date' => now(),
            ]);

            // Create file record
            File::create([
                'id_publication' => $publication->id_publication,
                'file_name' => $uniqueFilename,
                'file_source' => $fullPath,
                'mime_type' => $this->uploadedFile->getMimeType(),
                'file_size_bytes' => $this->uploadedFile->getSize(),
            ]);

            // Create registration log
            FileRegistrationLog::create([
                'publication_id' => $publication->id_publication,
                'file_path' => $fullPath,
                'registration_source' => 'admin_upload',
                'status' => 'processed',
                'registered_by' => auth()->id(),
            ]);

            session()->flash('message', __('File uploaded successfully'));
            $this->dispatch('file-uploaded-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'uploadedFile']);

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
