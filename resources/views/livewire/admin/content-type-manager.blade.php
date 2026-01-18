<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Content Types') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Manage content types and their custom fields') }}
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
            <button wire:click="createContentType" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                {{ __('Create Content Type') }}
            </button>
        </div>

        <!-- Content Types Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Icon') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Name (EN)') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Name (RU)') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Name (HE)') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Slug') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Custom Fields') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('System') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($contentTypes as $contentType)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-2xl">
                                @if($contentType->icon)
                                    <span title="{{ $contentType->icon }}">{{ $contentType->icon }}</span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex items-center gap-2">
                                    <span>{{ $contentType->name_en }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $contentType->name_ru ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $contentType->name_he ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $contentType->slug }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $contentType->custom_fields_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                @if($contentType->is_system)
                                    <span class="text-yellow-600">🔒 {{ __('System') }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('admin.content-types.fields', $contentType->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    {{ __('Manage Fields') }}
                                </a>
                                <button wire:click="editContentType({{ $contentType->id }})"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                    {{ __('Edit') }}
                                </button>
                                @if(!$contentType->is_system)
                                    <button wire:click="deleteContentType({{ $contentType->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this content type?') }}"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">
                                        {{ __('Delete') }}
                                    </button>
                                @else
                                    <span class="text-gray-400">{{ __('Cannot Delete') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No content types found.') }}
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
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $editingContentType ? __('Edit Content Type') : __('Create Content Type') }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveContentType">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Name EN -->
                        <div>
                            <label for="name_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Name (EN)') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="name_en" id="name_en"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('name_en') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Name RU -->
                        <div>
                            <label for="name_ru" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Name (RU)') }}
                            </label>
                            <input type="text" wire:model="name_ru" id="name_ru"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('name_ru') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Name HE -->
                        <div>
                            <label for="name_he" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Name (HE)') }}
                            </label>
                            <input type="text" wire:model="name_he" id="name_he"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('name_he') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Slug') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="slug" id="slug"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('slug') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Icon -->
                        <div x-data="{ showPicker: false }">
                            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Icon') }}
                            </label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="icon" id="icon" placeholder="📚"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-2xl">
                                <button type="button" @click="showPicker = !showPicker"
                                        class="mt-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                                    😀
                                </button>
                            </div>

                            <!-- Emoji Picker -->
                            <div x-show="showPicker" @click.outside="showPicker = false"
                                 class="mt-2 p-3 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 max-w-md">
                                <div class="grid grid-cols-8 gap-2">
                                    @foreach(['📚', '📖', '📕', '📗', '📘', '📙', '📰', '📃', '📄', '📜', '📋', '📊', '📈', '📉', '🎓', '🎯', '🔬', '🔭', '🗂️', '📁', '📂', '🗃️', '📑', '📓', '📔', '📒', '📝', '✍️', '🖊️', '🖋️', '✏️', '📐'] as $emoji)
                                        <button type="button"
                                                wire:click="$set('icon', '{{ $emoji }}')"
                                                @click="showPicker = false"
                                                class="text-2xl hover:bg-gray-100 dark:hover:bg-gray-600 p-2 rounded transition">
                                            {{ $emoji }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <p class="mt-1 text-xs text-gray-500">{{ __('Click the emoji button or type your own') }}</p>
                            @error('icon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Folder Name -->
                        <div>
                            <label for="folder_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Folder Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="folder_name" id="folder_name" placeholder="content"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500">{{ __('Use same folder for all types if files are stored together (e.g., "content")') }}</p>
                            @error('folder_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Is System -->
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="is_system" id="is_system"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <label for="is_system" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                {{ __('System Content Type') }}
                            </label>
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
                            {{ $editingContentType ? __('Update') : __('Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
