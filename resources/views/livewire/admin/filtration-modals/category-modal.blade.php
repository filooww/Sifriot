{{-- Modal Overlay --}}
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4"
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
                @if($editingCategory)
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/20">
                        <x-heroicon-o-pencil-square class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                        <x-heroicon-o-folder-plus class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                @endif
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $editingCategory ? __('Edit Category') : __('Create Category') }}
                </h3>
            </div>
            <button type="button" wire:click="closeModal"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        {{-- Modal Body --}}
        <form wire:submit.prevent="saveCategory">
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
                                <label for="category_name_en" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('English') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="category_name_en" id="category_name_en"
                                       x-on:input="$wire.category_slug = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('category_name_en') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            {{-- Name RU --}}
                            <div>
                                <label for="category_name_ru" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('Russian') }}
                                </label>
                                <input type="text" wire:model="category_name_ru" id="category_name_ru"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('category_name_ru') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            {{-- Name HE --}}
                            <div>
                                <label for="category_name_he" class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ __('Hebrew') }}
                                </label>
                                <input type="text" wire:model="category_name_he" id="category_name_he" dir="rtl"
                                       class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                @error('category_name_he') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Slug --}}
                    <div>
                        <label for="category_slug" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-o-link class="h-4 w-4" />
                                {{ __('Slug') }} <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="text" wire:model="category_slug" id="category_slug"
                               class="block w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                        @error('category_slug') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Parent & Sort Order Row --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        {{-- Parent Category --}}
                        <div>
                            <label for="category_parent_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-folder class="h-4 w-4" />
                                    {{ __('Parent Category') }}
                                </span>
                            </label>
                            <select wire:model="category_parent_id" id="category_parent_id"
                                    class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                                <option value="">- {{ __('Top Level') }} -</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name_en }}</option>
                                @endforeach
                            </select>
                            @error('category_parent_id') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="category_sort_order" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                                    {{ __('Sort Order') }}
                                </span>
                            </label>
                            <input type="number" wire:model="category_sort_order" id="category_sort_order" min="0"
                                   class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400">
                            @error('category_sort_order') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
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
                    @if($editingCategory)
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
