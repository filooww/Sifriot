{{-- Modal Overlay --}}
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
                                <label for="ct_name_en" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('English') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="ct_name_en" id="ct_name_en"
                                       x-on:input="$wire.ct_slug = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('ct_name_en') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            {{-- Name RU --}}
                            <div>
                                <label for="ct_name_ru" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('Russian') }}
                                </label>
                                <input type="text" wire:model="ct_name_ru" id="ct_name_ru"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('ct_name_ru') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            {{-- Name HE --}}
                            <div>
                                <label for="ct_name_he" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('Hebrew') }}
                                </label>
                                <input type="text" wire:model="ct_name_he" id="ct_name_he" dir="rtl"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('ct_name_he') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Slug & Icon Row --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        {{-- Slug --}}
                        <div>
                            <label for="ct_slug" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-link class="h-4 w-4" />
                                    {{ __('Slug') }} <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input type="text" wire:model="ct_slug" id="ct_slug"
                                   class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                            @error('ct_slug') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Icon --}}
                        <div>
                            <label for="ct_icon" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-face-smile class="h-4 w-4" />
                                    {{ __('Icon') }}
                                </span>
                            </label>
                            <div class="relative flex gap-2">
                                <div class="flex flex-1 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                                    {{-- Icon Preview --}}
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center">
                                        @if($ct_icon)
                                            @php
                                                $isEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ct_icon);
                                            @endphp
                                            @if($isEmoji)
                                                <span class="text-2xl">{{ $ct_icon }}</span>
                                            @else
                                                <x-dynamic-component :component="'heroicon-o-' . $ct_icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                            @endif
                                        @else
                                            <x-heroicon-o-photo class="h-6 w-6 text-gray-400" />
                                        @endif
                                    </div>
                                    <input type="text" wire:model.live="ct_icon" id="ct_icon" placeholder="book-open"
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
                                                    wire:click="$set('ct_icon', '{{ $iconName }}')"
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
                                                    wire:click="$set('ct_icon', '{{ $emoji }}')"
                                                    @click="showPicker = false"
                                                    class="rounded-lg p-1.5 text-xl transition hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ $emoji }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('ct_icon') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Folder Name --}}
                    <div>
                        <label for="ct_folder_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-o-folder class="h-4 w-4" />
                                {{ __('Folder Name') }}
                            </span>
                        </label>
                        <input type="text" wire:model="ct_folder_name" id="ct_folder_name" placeholder="content"
                               class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                        @error('ct_folder_name') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Is System Checkbox --}}
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                        <input type="checkbox" wire:model="ct_is_system" id="ct_is_system"
                               class="h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600">
                        <div>
                            <label for="ct_is_system" class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
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
