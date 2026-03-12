<?php

namespace App\Livewire\Admin;

use App\Models\FileMetadata;
use Livewire\Component;

class MetadataReviewPage extends Component
{
    public FileMetadata $fileMetadata;

    public function mount(FileMetadata $fileMetadata)
    {
        $this->fileMetadata = $fileMetadata;
    }

    public function render()
    {
        return view('livewire.admin.metadata-review-page')->layout('layouts.app');
    }
}
