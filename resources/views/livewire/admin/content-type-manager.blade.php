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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contentType->icon)
                                    @php
                                        $icon = $contentType->icon;
                                        $isEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $icon);
                                    @endphp
                                    @if($isEmoji)
                                        <span class="text-3xl" title="{{ $icon }}">{{ $icon }}</span>
                                    @else
                                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-7 w-7 text-blue-600 dark:text-blue-400" />
                                    @endif
                                @else
                                    <x-heroicon-o-document class="h-7 w-7 text-gray-400 dark:text-gray-500" />
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
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4"
             x-data="{ showPicker: false }"
             x-on:keydown.escape.window="$wire.closeModal()">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm dark:bg-black/70"
                 wire:click="closeModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            {{-- Modal Panel --}}
            <div class="relative z-10 w-full max-w-xl rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10"
                 wire:click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        @if($editingContentType)
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/20">
                                <x-heroicon-o-pencil-square class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                <x-heroicon-o-plus class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        @endif
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $editingContentType ? __('Edit Content Type') : __('Create Content Type') }}
                        </h3>
                    </div>
                    <button type="button" wire:click="closeModal"
                            class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit.prevent="saveContentType">
                    <div class="max-h-[calc(100vh-14rem)] overflow-y-auto px-6 py-5">
                        <div class="space-y-5">
                            {{-- Name Fields Group --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                                <h4 class="mb-3 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <x-heroicon-o-language class="h-4 w-4" />
                                    {{ __('Localized Names') }}
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-3">
                                    {{-- Name EN --}}
                                    <div>
                                        <label for="name_en" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                            {{ __('English') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="name_en" id="name_en"
                                               x-on:input="$wire.slug = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')"
                                               class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                        @error('name_en') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Name RU --}}
                                    <div>
                                        <label for="name_ru" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                            {{ __('Russian') }}
                                        </label>
                                        <input type="text" wire:model="name_ru" id="name_ru"
                                               class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                        @error('name_ru') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Name HE --}}
                                    <div>
                                        <label for="name_he" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                            {{ __('Hebrew') }}
                                        </label>
                                        <input type="text" wire:model="name_he" id="name_he" dir="rtl"
                                               class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                        @error('name_he') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Slug & Icon Row --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Slug --}}
                                <div>
                                    <label for="slug" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-link class="h-4 w-4" />
                                            {{ __('Slug') }} <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    <input type="text" wire:model="slug" id="slug"
                                           class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                    @error('slug') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                </div>

                                {{-- Icon --}}
                                <div>
                                    <label for="icon" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-face-smile class="h-4 w-4" />
                                            {{ __('Icon') }}
                                        </span>
                                    </label>
                                    <div class="relative flex gap-2">
                                        <div class="flex flex-1 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                                            {{-- Icon Preview --}}
                                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center">
                                                @if($icon)
                                                    @php
                                                        $isEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $icon);
                                                    @endphp
                                                    @if($isEmoji)
                                                        <span class="text-2xl">{{ $icon }}</span>
                                                    @else
                                                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                                    @endif
                                                @else
                                                    <x-heroicon-o-photo class="h-6 w-6 text-gray-400" />
                                                @endif
                                            </div>
                                            <input type="text" wire:model.live="icon" id="icon" placeholder="book-open"
                                                   class="flex-1 border-0 bg-transparent p-0 text-sm focus:ring-0 dark:text-white dark:placeholder-gray-400">
                                        </div>
                                        <button type="button" @click="showPicker = !showPicker"
                                                class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-gray-200">
                                            <x-heroicon-o-squares-plus class="h-5 w-5" />
                                        </button>

                                        {{-- Icon Picker Dropdown --}}
                                        <div x-show="showPicker" @click.outside="showPicker = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="absolute right-0 top-full z-20 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-600 dark:bg-gray-700">

                                            {{-- Heroicons Section --}}
                                            <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Icons') }}</p>
                                            <div class="mb-3 grid grid-cols-8 gap-1">
                                                @foreach(['book-open', 'newspaper', 'document-text', 'folder', 'academic-cap', 'bookmark', 'clipboard-document-list', 'document', 'document-duplicate', 'folder-open', 'inbox-stack', 'pencil-square', 'photo', 'presentation-chart-bar', 'rectangle-stack', 'tag'] as $iconName)
                                                    <button type="button"
                                                            wire:click="$set('icon', '{{ $iconName }}')"
                                                            @click="showPicker = false"
                                                            class="flex items-center justify-center rounded-lg p-2 transition hover:bg-gray-100 dark:hover:bg-gray-600"
                                                            title="{{ $iconName }}">
                                                        <x-dynamic-component :component="'heroicon-o-' . $iconName" class="h-5 w-5 text-gray-600 dark:text-gray-300" />
                                                    </button>
                                                @endforeach
                                            </div>

                                            {{-- Emojis Section --}}
                                            <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Emojis') }}</p>
                                            <div class="grid grid-cols-8 gap-1">
                                                @foreach(['📚', '📖', '📕', '📗', '📘', '📙', '📰', '📃', '📄', '📜', '📋', '📊', '🎓', '🎯', '📝', '📁'] as $emoji)
                                                    <button type="button"
                                                            wire:click="$set('icon', '{{ $emoji }}')"
                                                            @click="showPicker = false"
                                                            class="rounded-lg p-1.5 text-xl transition hover:bg-gray-100 dark:hover:bg-gray-600">
                                                        {{ $emoji }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @error('icon') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Folder Name --}}
                            <div>
                                <label for="folder_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-1.5">
                                        <x-heroicon-o-folder class="h-4 w-4" />
                                        {{ __('Folder Name') }} <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input type="text" wire:model="folder_name" id="folder_name" placeholder="content"
                                       class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Use same folder for all types if files are stored together (e.g., "content")') }}</p>
                                @error('folder_name') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            {{-- Is System Checkbox --}}
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                                <input type="checkbox" wire:model="is_system" id="is_system"
                                       class="h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600">
                                <div>
                                    <label for="is_system" class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                        <x-heroicon-o-lock-closed class="h-4 w-4 text-amber-500" />
                                        {{ __('System Content Type') }}
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('System types cannot be deleted') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50/80 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <button type="button" wire:click="closeModal"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800">
                            @if($editingContentType)
                                <x-heroicon-o-check class="h-4 w-4" />
                                {{ __('Update') }}
                            @else
                                <x-heroicon-o-plus class="h-4 w-4" />
                                {{ __('Create') }}
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
