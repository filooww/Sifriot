<div x-data="{
    openSections: {
        contentType: true,
        author: false,
        date: false,
        genre: false,
        textSize: false,
        alphabetical: false,
        status: false,
        extractionStatus: false,
        format: false,
        extractionDate: false
    }
}" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 sticky top-4">

    {{-- Filter Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-4 py-3 rounded-t-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h3 class="text-lg font-semibold text-white">
                    {{ __('Filters') }}
                </h3>
            </div>
            <button
                wire:click="clearAllFilters"
                class="text-sm text-white hover:text-blue-100 transition-colors font-medium px-3 py-1 bg-white/20 hover:bg-white/30 rounded-md"
            >
                {{ __('Clear all filters') }}
            </button>
        </div>
    </div>

    <div class="p-4 max-h-[calc(100vh-200px)] overflow-y-auto">
        {{-- Applied Filters Tags --}}
        @if(count($this->appliedFilters) > 0)
        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Active Filters</p>
            <div class="flex flex-wrap gap-2">
                @foreach($this->appliedFilters as $filter)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-lg border border-blue-200 dark:border-blue-700">
                    {{ $filter['label'] }}
                    <button
                        wire:click="removeFilter('{{ $filter['type'] }}', '{{ is_array($filter['value']) ? json_encode($filter['value']) : $filter['value'] }}')"
                        class="hover:text-blue-900 dark:hover:text-blue-100 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Content Type Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.contentType = !openSections.contentType"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ __('Content Type') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.contentType ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.contentType" x-transition class="mt-2 space-y-1.5 pl-2">
                @foreach($this->contentTypes as $contentType)
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="selectedContentTypes"
                        value="{{ $contentType['id'] }}"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ $contentType['icon'] ?? '' }} {{ $contentType['name_' . app()->getLocale()] ?? $contentType['name_en'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Author Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.author = !openSections.author"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ __('Author') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.author ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.author" x-transition class="mt-2">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="authorSearchQuery"
                    placeholder="{{ __('Search authors...') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                >
                @if(count($this->authorSearchResults) > 0)
                <div class="mt-2 space-y-1">
                    @foreach($this->authorSearchResults as $author)
                    <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model.live="selectedAuthors"
                            value="{{ $author['id'] }}"
                            class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                        >
                        <span class="text-sm">{{ $author['name'] }}</span>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Date Range Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.date = !openSections.date"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ __('Date Range') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.date ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.date" x-transition class="mt-2 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('From') }}</label>
                    <input
                        type="date"
                        wire:model.live="dateFrom"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    >
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('To') }}</label>
                    <input
                        type="date"
                        wire:model.live="dateTo"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    >
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Genre Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.genre = !openSections.genre"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>{{ __('Genre') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.genre ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.genre" x-transition class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                @foreach($this->genres as $genre)
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="selectedGenres"
                        value="{{ $genre['id_theme'] }}"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ $genre['theme'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Text Size Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.textSize = !openSections.textSize"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('Text Size') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.textSize ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.textSize" x-transition x-data="{
                min: @entangle('textSizeRange.0'),
                max: @entangle('textSizeRange.1')
            }" class="mt-2">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="min.toLocaleString() + ' ' + '{{ __('words') }}'"></span>
                        <span x-text="max.toLocaleString() + ' ' + '{{ __('words') }}'"></span>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Min</label>
                        <input
                            type="range"
                            x-model="min"
                            min="0"
                            max="500000"
                            step="1000"
                            class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        >
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Max</label>
                        <input
                            type="range"
                            x-model="max"
                            min="0"
                            max="500000"
                            step="1000"
                            class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Alphabetical Sort Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.alphabetical = !openSections.alphabetical"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/>
                    </svg>
                    <span>{{ __('Alphabetical') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.alphabetical ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.alphabetical" x-transition class="mt-2 space-y-2">
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="alphabeticalSort"
                        value="asc"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('A-Z') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="alphabeticalSort"
                        value="desc"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Z-A') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="alphabeticalSort"
                        value=""
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('None') }}</span>
                </label>
            </div>
        </div>

        {{-- Metadata Filters (Admin Only) --}}
        @if(!$hideAdminFilters && auth()->check() && auth()->user()->role === 'admin')

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Extraction Status Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.extractionStatus = !openSections.extractionStatus"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('Extraction Status') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.extractionStatus ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.extractionStatus" x-transition class="mt-2 space-y-2">
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="all"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('All') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="pending"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">⏳ {{ __('Pending') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="processed"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">📋 {{ __('Ready for Review') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="confirmed"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">✅ {{ __('Confirmed') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="failed"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">❌ {{ __('Failed') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="statusFilter"
                        value="rejected"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">🚫 {{ __('Rejected') }}</span>
                </label>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- File Format Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.format = !openSections.format"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('File Format') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.format ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.format" x-transition class="mt-2 space-y-2">
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="all"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('All') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="pdf"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">PDF</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="epub"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">EPUB</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="txt"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">TXT</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="doc"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">DOC</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="docx"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">DOCX</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="fb2"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">FB2</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="formatFilter"
                        value="djvu"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">DJVU</span>
                </label>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Extraction Date Filter --}}
        <div class="mb-3">
            <button
                @click="openSections.extractionDate = !openSections.extractionDate"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ __('Extraction Date') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.extractionDate ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.extractionDate" x-transition class="mt-2 space-y-2">
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="dateFilter"
                        value="all"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('All Time') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="dateFilter"
                        value="1day"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Last 24h') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="dateFilter"
                        value="7days"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Last 7 days') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="radio"
                        wire:model.live="dateFilter"
                        value="30days"
                        class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Last 30 days') }}</span>
                </label>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

        {{-- Publication Status Filter (Admin Only) --}}
        <div class="mb-3">
            <button
                @click="openSections.status = !openSections.status"
                class="w-full flex items-center justify-between text-start font-semibold text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('Publication Status') }}</span>
                </div>
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="openSections.status ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.status" x-transition class="mt-2 space-y-2">
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="publicationStatus"
                        value="published"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Published') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="publicationStatus"
                        value="hidden"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Hidden') }}</span>
                </label>
                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="publicationStatus"
                        value="pending"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 focus:ring-2"
                    >
                    <span class="text-sm">{{ __('Pending') }}</span>
                </label>
            </div>
        </div>
        @endif
    </div>
</div>
