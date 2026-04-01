<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">{{ __('File Registration') }}</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Registration Form -->
    <form wire:submit.prevent="uploadFile" class="space-y-6">

            <!-- Upload File Section -->
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Select File') }}</label>
                <input type="file" wire:model="uploadedFile" class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                @error('uploadedFile') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Max file size: 500MB') }}<br>
                    {{ __('Allowed formats: PDF, EPUB, TXT, DOCX') }}
                </p>

                <!-- Upload Progress Indicator -->
                <div wire:loading wire:target="uploadedFile" class="mt-2">
                    <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded">
                        {{ __('Uploading...') }}
                    </div>
                </div>
            </div>

        <!-- Publication Title -->
        <div>
            <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Title') }}</label>
            <input type="text" wire:model="publicationTitle" class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            @error('publicationTitle') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Content Type Selection -->
        <div x-data="{ open: false, selectedId: @entangle('contentTypeId') }" class="relative">
            <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Content Type') }}</label>
            <button
                type="button"
                @click="open = !open"
                @click.outside="open = false"
                class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-left flex items-center justify-between"
            >
                <span class="flex items-center gap-2">
                    @foreach($contentTypes as $type)
                        <template x-if="selectedId == {{ $type->id }}">
                            <span class="flex items-center gap-2">
                                @if($type->icon)
                                    @php
                                        $ctIcon = $type->icon;
                                        $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                                    @endphp
                                    @if($ctIsEmoji)
                                        <span>{{ $ctIcon }}</span>
                                    @else
                                        <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    @endif
                                @endif
                                <span>{{ (app()->getLocale() === 'ru' && $type->name_ru) ? $type->name_ru : ((app()->getLocale() === 'he' && $type->name_he) ? $type->name_he : $type->name_en) }}</span>
                            </span>
                        </template>
                    @endforeach
                    <template x-if="!selectedId">
                        <span class="text-gray-500">{{ __('Select Content Type') }}</span>
                    </template>
                </span>
                <x-heroicon-o-chevron-down class="w-5 h-5 text-gray-400" />
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
            >
                <button
                    type="button"
                    @click="selectedId = ''; open = false"
                    class="w-full px-3 py-2 text-left text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600"
                >
                    {{ __('Select Content Type') }}
                </button>
                @foreach($contentTypes as $type)
                    <button
                        type="button"
                        @click="selectedId = {{ $type->id }}; open = false"
                        class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-2"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/30': selectedId == {{ $type->id }} }"
                    >
                        @if($type->icon)
                            @php
                                $ctIcon = $type->icon;
                                $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                            @endphp
                            @if($ctIsEmoji)
                                <span>{{ $ctIcon }}</span>
                            @else
                                <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            @endif
                        @endif
                        <span class="text-gray-900 dark:text-white">{{ (app()->getLocale() === 'ru' && $type->name_ru) ? $type->name_ru : ((app()->getLocale() === 'he' && $type->name_he) ? $type->name_he : $type->name_en) }}</span>
                    </button>
                @endforeach
            </div>
            @error('contentTypeId') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-700 dark:hover:bg-blue-800">
                {{ __('Upload File') }}
            </button>

            <a href="{{ route('admin.files.browse') }}" class="bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-6 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600">
                {{ __('Cancel') }}
            </a>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="uploadFile" class="mt-4">
            <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded">
                {{ __('Processing...') }}
            </div>
        </div>
    </form>
</div>
