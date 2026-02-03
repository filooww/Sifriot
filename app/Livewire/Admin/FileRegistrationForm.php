<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\ContentType;
use App\Models\File;
use App\Models\FileRegistrationLog;
use App\Models\Publication;
use App\Services\FileStorageService;
use Illuminate\Support\Facades\DB;
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
            'selectedFilePath' => 'required|string',
            'publicationTitle' => 'required|max:500',
            'contentTypeId' => 'required|exists:content_types,id',
        ]);

        try {
            // Check duplicate file path
            $fullPath = Storage::disk('library')->path($this->selectedFilePath);
            if (FileRegistrationLog::where('file_path', $fullPath)->exists()) {
                session()->flash('error', __('This file is already registered'));

                return;
            }

            // Get file metadata before transaction
            $fileName = basename($this->selectedFilePath);
            $mimeType = Storage::disk('library')->mimeType($this->selectedFilePath);
            $fileSize = Storage::disk('library')->size($this->selectedFilePath);

            // Wrap all database operations in a transaction
            DB::transaction(function () use ($fullPath, $fileName, $mimeType, $fileSize) {
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
                    'file_name' => $fileName,
                    'file_source' => $fullPath,
                    'mime_type' => $mimeType,
                    'file_size_bytes' => $fileSize,
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
                    $fileId = "{$publication->id_publication}-{$fileName}";
                    ExtractMetadataFromFile::dispatch(
                        $fileId,
                        $fullPath,
                        $this->contentTypeId,
                        $mimeType
                    );
                }
            });

            session()->flash('message', __('File registered successfully. Metadata extraction started...'));
            $this->dispatch('file-registered-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'selectedFilePath']);

        } catch (\Exception $e) {
            Log::error('File registration failed', [
                'error' => $e->getMessage(),
                'file_path' => $this->selectedFilePath,
                'trace' => $e->getTraceAsString(),
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

        $filePath = null;

        try {
            $contentType = ContentType::find($this->contentTypeId);
            $extension = $this->uploadedFile->getClientOriginalExtension();
            $originalName = $this->uploadedFile->getClientOriginalName();

            // Generate unique filename
            $uniqueFilename = hash('sha256', $originalName.time()).'.'.$extension;

            // Determine storage path: month-year/content_type (e.g., 02-2026/books)
            $monthYear = now()->format('m-Y'); // e.g., 02-2026
            $storagePath = $monthYear.'/'.$contentType->folder_name;

            // Ensure the directory exists before storing the file
            if (! Storage::disk('library')->exists($storagePath)) {
                Storage::disk('library')->makeDirectory($storagePath, 0755, true);
            }

            // Store uploaded file in library disk (D:\oldI\LiteraCommon)
            $filePath = $this->uploadedFile->storeAs($storagePath, $uniqueFilename, 'library');

            if (! $filePath) {
                throw new \RuntimeException('Failed to store uploaded file');
            }

            $fullPath = Storage::disk('library')->path($filePath);
            $mimeType = $this->uploadedFile->getMimeType();
            $fileSize = $this->uploadedFile->getSize();

            // Wrap all database operations in a transaction
            DB::transaction(function () use ($uniqueFilename, $fullPath, $mimeType, $fileSize) {
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
                    'mime_type' => $mimeType,
                    'file_size_bytes' => $fileSize,
                ]);

                // Create registration log
                FileRegistrationLog::create([
                    'publication_id' => $publication->id_publication,
                    'file_path' => $fullPath,
                    'registration_source' => 'admin_upload',
                    'status' => 'processed',
                    'registered_by' => auth()->id(),
                ]);

                // Dispatch metadata extraction job if enabled
                if (config('library.extraction.enabled', true)) {
                    $fileId = "{$publication->id_publication}-{$uniqueFilename}";
                    ExtractMetadataFromFile::dispatch(
                        $fileId,
                        $fullPath,
                        $this->contentTypeId,
                        $mimeType
                    );
                }
            });

            session()->flash('message', __('File uploaded successfully. Metadata extraction started...'));
            $this->dispatch('file-uploaded-successfully');
            $this->reset(['publicationTitle', 'contentTypeId', 'uploadedFile']);

        } catch (\Exception $e) {
            // Clean up the uploaded file if database operations failed
            if ($filePath && Storage::disk('library')->exists($filePath)) {
                Storage::disk('library')->delete($filePath);
            }

            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
