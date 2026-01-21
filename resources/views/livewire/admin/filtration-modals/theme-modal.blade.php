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
    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10"
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
                @if($editingTheme)
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/20">
                        <x-heroicon-o-pencil-square class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-500/20">
                        <x-heroicon-o-sparkles class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                @endif
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $editingTheme ? __('Edit Theme') : __('Create Theme') }}
                </h3>
            </div>
            <button type="button" wire:click="closeModal"
                    class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        {{-- Modal Body --}}
        <form wire:submit.prevent="saveTheme">
            <div class="px-6 py-5">
                <div class="space-y-5">
                    {{-- Theme Name --}}
                    <div>
                        <label for="theme_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-o-tag class="h-4 w-4" />
                                {{ __('Theme') }} <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="text" wire:model="theme_name" id="theme_name"
                               x-on:input="$wire.theme_name_low = $event.target.value.toLowerCase()"
                               class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm transition focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400">
                        @error('theme_name') <span class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Theme Name Lowercase (readonly) --}}
                    <div>
                        <label for="theme_name_low" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-o-code-bracket class="h-4 w-4" />
                                {{ __('Theme (lowercase)') }}
                            </span>
                        </label>
                        <input type="text" wire:model="theme_name_low" id="theme_name_low" readonly
                               class="block w-full cursor-not-allowed rounded-lg border-gray-300 bg-gray-100 font-mono text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-400">
                        <p class="mt-1.5 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-information-circle class="h-3.5 w-3.5" />
                            {{ __('Auto-generated from theme name') }}
                        </p>
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
                    @if($editingTheme)
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
