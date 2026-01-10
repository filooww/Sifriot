<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with Breadcrumb -->
        <div class="mb-6">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.content-types') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300">
                            {{ __('Content Types') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2 text-gray-400">/</span>
                            <span class="text-gray-900 dark:text-white">{{ $contentType->name_en }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Custom Fields for') }} {{ $contentType->name_en }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Define custom metadata fields for this content type') }}
            </p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Create Button -->
        <div class="mb-6">
            <button wire:click="createField" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                {{ __('Create Custom Field') }}
            </button>
        </div>

        <!-- Custom Fields Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Sort Order') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Field Name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Label (EN)') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Type') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Required') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Visibility') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Searchable') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Filterable') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($customFields as $field)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $field->sort_order }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">
                                {{ $field->field_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $field->label_en }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 rounded">
                                    {{ $field->field_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $field->is_required ? '✓' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ ucfirst(str_replace('_', ' ', $field->visibility)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $field->is_searchable ? '✓' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $field->is_filterable ? '✓' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="editField({{ $field->id }})"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                    {{ __('Edit') }}
                                </button>
                                <button wire:click="deleteField({{ $field->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this custom field? All associated values will be lost.') }}"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400">
                                    {{ __('Delete') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No custom fields defined for this content type.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $editingField ? __('Edit Custom Field') : __('Create Custom Field') }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveField">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Field Name -->
                        <div class="md:col-span-2">
                            <label for="field_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Field Name') }} <span class="text-red-500">*</span>
                                <span class="text-xs text-gray-500">({{ __('slug format, no spaces') }})</span>
                            </label>
                            <input type="text" wire:model="field_name" id="field_name"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono">
                            @error('field_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Label EN -->
                        <div>
                            <label for="label_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Label (EN)') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="label_en" id="label_en"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('label_en') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Label RU -->
                        <div>
                            <label for="label_ru" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Label (RU)') }}
                            </label>
                            <input type="text" wire:model="label_ru" id="label_ru"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('label_ru') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Label HE -->
                        <div>
                            <label for="label_he" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Label (HE)') }}
                            </label>
                            <input type="text" wire:model="label_he" id="label_he" dir="rtl"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('label_he') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Field Type -->
                        <div>
                            <label for="field_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Field Type') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="field_type" id="field_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="text">{{ __('Text') }}</option>
                                <option value="number">{{ __('Number') }}</option>
                                <option value="date">{{ __('Date') }}</option>
                                <option value="dropdown">{{ __('Dropdown') }}</option>
                                <option value="multiselect">{{ __('Multiselect') }}</option>
                                <option value="boolean">{{ __('Boolean') }}</option>
                                <option value="long_text">{{ __('Long Text') }}</option>
                            </select>
                            @error('field_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Field Config (JSON) -->
                        <div class="md:col-span-2">
                            <label for="config_json" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Field Configuration') }} <span class="text-xs text-gray-500">({{ __('JSON format') }})</span>
                            </label>
                            <textarea wire:model="config_json" id="config_json" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono text-sm"></textarea>
                            @error('config_json') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Example for dropdown: {"options":[{"value":"opt1","label_en":"Option 1"}]}') }}
                            </p>
                        </div>

                        <!-- Visibility -->
                        <div>
                            <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Visibility') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="visibility" id="visibility"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="public">{{ __('Public') }}</option>
                                <option value="admin_only">{{ __('Admin Only') }}</option>
                                <option value="hidden">{{ __('Hidden') }}</option>
                            </select>
                            @error('visibility') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Sort Order') }}
                            </label>
                            <input type="number" wire:model="sort_order" id="sort_order" min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('sort_order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Checkboxes -->
                        <div class="md:col-span-2 space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_required" id="is_required"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_required" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    {{ __('Is Required') }}
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_searchable" id="is_searchable"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_searchable" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    {{ __('Is Searchable') }}
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_filterable" id="is_filterable"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_filterable" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    {{ __('Is Filterable') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                            {{ $editingField ? __('Update') : __('Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
