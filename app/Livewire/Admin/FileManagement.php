<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Attributes\On;
use Livewire\Component;

class FileManagement extends Component
{
    public string $activeTab = 'browse';

    public ?string $selectedFilePath = null;

    public ?int $currentScanJobId = null;

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On('file-selected-for-registration')]
    public function onFileSelectedForRegistration(string $filePath): void
    {
        $this->selectedFilePath = $filePath;
        $this->activeTab = 'upload';
    }

    #[On('scan-job-created')]
    public function onScanJobCreated(int $scanJobId): void
    {
        $this->currentScanJobId = $scanJobId;
    }

    #[On('metadata-confirmed')]
    public function onMetadataConfirmed(): void
    {
        // Refresh metadata queue when item confirmed
        $this->dispatch('refresh-metadata-queue');
    }

    public function render()
    {
        return view('livewire.admin.file-management', [
            'selectedFilePath' => $this->selectedFilePath,
            'currentScanJobId' => $this->currentScanJobId,
        ]);
    }
}
