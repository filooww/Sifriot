<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;

class FileManagement extends Component
{
    public string $activeTab = 'browse';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.file-management');
    }
}
