<?php

declare(strict_types=1);

namespace App\Livewire\Publications;

use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentViewer extends Component
{
    public int $publicationId;

    public ?string $selectedFileName = null;

    public string $viewerType = 'none'; // 'pdf', 'epub', 'text', 'document', 'fb2', 'djvu', 'none'

    public bool $isAuthorized = false;

    public string $fileUrl = '';

    public ?string $fileName = null;

    public ?string $mimeType = null;

    public ?int $fileSizeBytes = null;

    public function mount(int $publicationId, ?string $fileName = null): void
    {
        $this->publicationId = $publicationId;
        // Check authorization
        $this->isAuthorized = Auth::check();

        if ($this->isAuthorized) {
            $file = null;

            if ($fileName) {
                // Primary: strict match on original filename
                $file = File::where('id_publication', $publicationId)
                    ->where('file_name', $fileName)
                    ->first();

                // Fallback: case-insensitive match via file_name_low
                if (! $file) {
                    $file = File::where('id_publication', $publicationId)
                        ->where('file_name_low', mb_strtolower($fileName))
                        ->first();
                }
            } else {
                // No specific file requested: use first content file (exclude covers)
                $file = File::where('id_publication', $publicationId)
                    ->where(function ($query) {
                        $query->whereNull('file_type')
                            ->orWhere('file_type', '')
                            ->orWhere('file_type', '!=', 'cover');
                    })
                    ->orderBy('ord_num')
                    ->first();
            }

            if ($file) {
                $this->selectedFileName = $file->file_name;
                $this->setFileData($file);
            }
        }
    }

    #[On('fileSelected')]
    public function selectFile(string $fileName): void
    {
        if (! $this->isAuthorized) {
            return;
        }
        // Try strict match first, then case-insensitive fallback
        $file = File::where('id_publication', $this->publicationId)
            ->where('file_name', $fileName)
            ->first();

        if (! $file) {
            $file = File::where('id_publication', $this->publicationId)
                ->where('file_name_low', mb_strtolower($fileName))
                ->first();
        }

        if ($file) {
            $this->selectedFileName = $fileName;
            $this->setFileData($file);
        }
    }

    private function setFileData(File $file): void
    {
        $this->fileName = $file->file_name;
        $this->mimeType = $file->mime_type;
        $this->fileSizeBytes = $file->file_size_bytes;
        $this->determineViewerType();
        $this->generateFileUrl();
    }

    private function determineViewerType(): void
    {
        if (! $this->fileName) {
            \Log::warning('DocumentViewer: No filename provided for viewer type determination');
            $this->viewerType = 'none';

            return;
        }

        $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));

        \Log::info('DocumentViewer: Determining viewer type', [
            'filename' => $this->fileName,
            'extension' => $extension,
            'publication_id' => $this->publicationId,
        ]);

        $this->viewerType = match ($extension) {
            'pdf' => 'pdf',
            'epub' => 'epub',
            'txt' => 'text',
            'docx', 'doc' => 'document',
            'fb2' => 'fb2',
            'djvu' => 'djvu',
            default => 'none',
        };

        \Log::info('DocumentViewer: Viewer type determined', [
            'viewer_type' => $this->viewerType,
            'extension' => $extension,
        ]);
    }

    private function generateFileUrl(): void
    {
        if ($this->fileName && $this->isAuthorized) {
            // URL-safe base64 encode the filename to safely pass Cyrillic characters
            // Replace + with - and / with _ for URL safety, remove padding =
            $encodedFilename = rtrim(strtr(base64_encode($this->fileName), '+/', '-_'), '=');
            $this->fileUrl = route('files.view', [
                'publication' => $this->publicationId,
                'filename' => $encodedFilename,
            ]);
        }
    }

    public function encodeFileName(string $fileName): string
    {
        return rtrim(strtr(base64_encode($fileName), '+/', '-_'), '=');
    }

    public function render()
    {
        return view('livewire.publications.document-viewer');
    }
}
