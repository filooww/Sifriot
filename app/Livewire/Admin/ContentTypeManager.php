<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ContentType;
use Livewire\Attributes\On;
use Livewire\Component;

class ContentTypeManager extends Component
{
    public $contentTypes;

    public $showModal = false;

    public $editingContentType = null;

    // Form fields
    public $name_en = '';

    public $name_ru = '';

    public $name_he = '';

    public $slug = '';

    public $icon = '';

    public $folder_name = '';

    public $is_system = false;

    protected function rules(): array
    {
        $contentTypeId = $this->editingContentType?->id ?? null;

        return [
            'name_en' => 'required|string|max:255',
            'name_ru' => 'nullable|string|max:255',
            'name_he' => 'nullable|string|max:255',
            'slug' => "required|string|max:255|unique:content_types,slug,{$contentTypeId}",
            'icon' => 'nullable|string|max:255',
            'folder_name' => 'required|string|max:255',
            'is_system' => 'boolean',
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');
        $this->loadContentTypes();
    }

    public function loadContentTypes(): void
    {
        $this->contentTypes = ContentType::withCount('customFields')->get();
    }

    public function createContentType(): void
    {
        $this->resetForm();
        $this->editingContentType = null;
        $this->showModal = true;
    }

    public function editContentType(int $id): void
    {
        $this->editingContentType = ContentType::findOrFail($id);

        $this->name_en = $this->editingContentType->name_en;
        $this->name_ru = $this->editingContentType->name_ru ?? '';
        $this->name_he = $this->editingContentType->name_he ?? '';
        $this->slug = $this->editingContentType->slug;
        $this->icon = $this->editingContentType->icon ?? '';
        $this->folder_name = $this->editingContentType->folder_name;
        $this->is_system = $this->editingContentType->is_system;

        $this->showModal = true;
    }

    public function saveContentType(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        $validated = $this->validate();

        if ($this->editingContentType) {
            $this->editingContentType->update($validated);
            session()->flash('message', 'Content type updated successfully.');
        } else {
            ContentType::create($validated);
            session()->flash('message', 'Content type created successfully.');
        }

        $this->dispatch('content-type-saved');
        $this->showModal = false;
        $this->loadContentTypes();
        $this->resetForm();
    }

    public function deleteContentType(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        $contentType = ContentType::findOrFail($id);

        if ($contentType->is_system) {
            session()->flash('error', 'Cannot delete system content types.');

            return;
        }

        $contentType->delete();
        session()->flash('message', 'Content type deleted successfully.');
        $this->loadContentTypes();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name_en = '';
        $this->name_ru = '';
        $this->name_he = '';
        $this->slug = '';
        $this->icon = '';
        $this->folder_name = '';
        $this->is_system = false;
        $this->editingContentType = null;
    }

    #[On('content-type-saved')]
    public function handleContentTypeSaved(): void
    {
        $this->loadContentTypes();
    }

    public function render()
    {
        return view('livewire.admin.content-type-manager')->layout('layouts.app');
    }
}
