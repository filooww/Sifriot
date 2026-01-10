<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ContentType;
use App\Models\CustomField;
use Livewire\Component;
use Livewire\Attributes\On;

class CustomFieldManager extends Component
{
    public $contentTypeId;
    public $contentType;
    public $customFields;
    public $showModal = false;
    public $editingField = null;

    // Form fields
    public $field_name = '';
    public $label_en = '';
    public $label_ru = '';
    public $label_he = '';
    public $field_type = 'text';
    public $field_config = [];
    public $is_required = false;
    public $visibility = 'public';
    public $is_searchable = false;
    public $is_filterable = false;
    public $sort_order = 0;

    // For field_config JSON editing
    public $config_json = '';

    protected function rules(): array
    {
        $fieldId = $this->editingField?->id ?? null;

        return [
            'field_name' => "required|string|max:255|alpha_dash|unique:custom_fields,field_name,{$fieldId},id,content_type_id,{$this->contentTypeId}",
            'label_en' => 'required|string|max:255',
            'label_ru' => 'nullable|string|max:255',
            'label_he' => 'nullable|string|max:255',
            'field_type' => 'required|in:text,number,date,dropdown,multiselect,boolean,long_text',
            'config_json' => 'nullable|json',
            'is_required' => 'boolean',
            'visibility' => 'required|in:public,admin_only,hidden',
            'is_searchable' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function mount(int $contentTypeId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        $this->contentTypeId = $contentTypeId;
        $this->contentType = ContentType::findOrFail($contentTypeId);
        $this->loadCustomFields();
    }

    public function loadCustomFields(): void
    {
        $this->customFields = CustomField::where('content_type_id', $this->contentTypeId)
            ->orderedBySortOrder()
            ->get();
    }

    public function createField(): void
    {
        $this->resetForm();
        $this->editingField = null;
        $this->sort_order = $this->customFields->max('sort_order') + 1;
        $this->showModal = true;
    }

    public function editField(int $id): void
    {
        $this->editingField = CustomField::findOrFail($id);

        $this->field_name = $this->editingField->field_name;
        $this->label_en = $this->editingField->label_en;
        $this->label_ru = $this->editingField->label_ru ?? '';
        $this->label_he = $this->editingField->label_he ?? '';
        $this->field_type = $this->editingField->field_type;
        $this->field_config = $this->editingField->field_config ?? [];
        $this->config_json = !empty($this->field_config) ? json_encode($this->field_config, JSON_PRETTY_PRINT) : '';
        $this->is_required = $this->editingField->is_required;
        $this->visibility = $this->editingField->visibility;
        $this->is_searchable = $this->editingField->is_searchable;
        $this->is_filterable = $this->editingField->is_filterable;
        $this->sort_order = $this->editingField->sort_order;

        $this->showModal = true;
    }

    public function saveField(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        $validated = $this->validate();

        // Parse field_config from JSON string
        $fieldConfigArray = !empty($validated['config_json'])
            ? json_decode($validated['config_json'], true)
            : [];

        $data = [
            'content_type_id' => $this->contentTypeId,
            'field_name' => $validated['field_name'],
            'label_en' => $validated['label_en'],
            'label_ru' => $validated['label_ru'] ?? null,
            'label_he' => $validated['label_he'] ?? null,
            'field_type' => $validated['field_type'],
            'field_config' => $fieldConfigArray,
            'is_required' => $validated['is_required'],
            'visibility' => $validated['visibility'],
            'is_searchable' => $validated['is_searchable'],
            'is_filterable' => $validated['is_filterable'],
            'sort_order' => $validated['sort_order'],
        ];

        if ($this->editingField) {
            $this->editingField->update($data);
            session()->flash('message', 'Custom field updated successfully.');
        } else {
            CustomField::create($data);
            session()->flash('message', 'Custom field created successfully.');
        }

        $this->dispatch('custom-field-saved');
        $this->showModal = false;
        $this->loadCustomFields();
        $this->resetForm();
    }

    public function deleteField(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        $field = CustomField::findOrFail($id);
        $field->delete();

        session()->flash('message', 'Custom field deleted successfully.');
        $this->loadCustomFields();
    }

    public function reorderFields(array $orderedIds): void
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized');

        foreach ($orderedIds as $index => $id) {
            CustomField::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->loadCustomFields();
        session()->flash('message', 'Fields reordered successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->field_name = '';
        $this->label_en = '';
        $this->label_ru = '';
        $this->label_he = '';
        $this->field_type = 'text';
        $this->field_config = [];
        $this->config_json = '';
        $this->is_required = false;
        $this->visibility = 'public';
        $this->is_searchable = false;
        $this->is_filterable = false;
        $this->sort_order = 0;
        $this->editingField = null;
    }

    #[On('custom-field-saved')]
    public function handleCustomFieldSaved(): void
    {
        $this->loadCustomFields();
    }

    public function render()
    {
        return view('livewire.admin.custom-field-manager')->layout('layouts.app');
    }
}
