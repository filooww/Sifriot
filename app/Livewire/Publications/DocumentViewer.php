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

    public function mount(int $publicationId): void
    {
        $this->publicationId = $publicationId;

        // Check authorization
        $this->isAuthorized = Auth::check();

        // Auto-select first file if authorized
        if ($this->isAuthorized) {
            $firstFile = File::where('id_publication', $publicationId)
                ->orderBy('ord_num')
                ->first();

            if ($firstFile) {
                $this->selectedFileName = $firstFile->file_name;
                $this->setFileData($firstFile);
            }
        }
    }

    #[On('fileSelected')]
    public function selectFile(string $fileName): void
    {
        if (! $this->isAuthorized) {
            return;
        }

        $file = File::where('id_publication', $this->publicationId)
            ->where('file_name', $fileName)
            ->first();

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
            $this->viewerType = 'none';

            return;
        }

        $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));

        $this->viewerType = match ($extension) {
            'pdf' => 'pdf',
            'epub' => 'epub',
            'txt' => 'text',
            'docx', 'doc' => 'document',
            'fb2' => 'fb2',
            'djvu' => 'djvu',
            default => 'none',
        };
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
