<div x-data="{
    init() {
        // Load from localStorage on init
        const stored = localStorage.getItem('publicationFilters_openSections');
        if (stored) {
            this.openSections = JSON.parse(stored);
        }
        // Watch for changes and save to localStorage
        this.$watch('openSections', (value) => {
            localStorage.setItem('publicationFilters_openSections', JSON.stringify(value));
        }, { deep: true });
    },
    openSections: {
        contentType: true,
        author: false,
        date: false,
        genre: true,
        section: false,
        publisher: false,
        textSize: false,
        alphabetical: false,
        status: false,
        extractionStatus: false,
        format: false,
        extractionDate: false,
        realGenre: false
    },
    genreSearch: '',
    realGenreSearch: '',
    authorSearch: '',
    sectionSearch: '',
    publisherSearch: ''
}" :class="$isModal ? '' : 'bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700'">

    @if(!$isModal)
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
    @endif

    <div :class="$isModal ? 'space-y-2' : 'p-3 space-y-2 max-h-[calc(100vh-200px)] overflow-y-auto'">
        {{-- Applied Filters Tags --}}
        @if(count($this->appliedFilters) > 0)
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Active Filters') }}</p>
                <button wire:click="clearAllFilters" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('Clear all') }}
                </button>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach($this->appliedFilters as $filter)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs rounded-full border border-blue-200 dark:border-blue-700">
                    {{ Str::limit($filter['label'], 30) }}
                    <button
                        wire:click="removeFilter('{{ $filter['type'] }}', '{{ is_array($filter['value']) ? json_encode($filter['value']) : $filter['value'] }}')"
                        class="hover:text-blue-900 dark:hover:text-blue-100 transition-colors p-0.5 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Content Type Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.contentType = !openSections.contentType"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm">{{ __('Content Type') }}</span>
                    @if(count($this->selectedContentTypes) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-blue-500 text-white rounded-full">{{ count($this->selectedContentTypes) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.contentType ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.contentType" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 grid grid-cols-2 gap-1">
                    @foreach($this->contentTypes as $contentType)
                    <label class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            wire:model.live="selectedContentTypes"
                            value="{{ $contentType['id_content_type'] }}"
                            class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                        >
                        <span class="flex items-center gap-1 text-xs">
                            @if(!empty($contentType['icon']))
                                @php
                                    $ctIcon = $contentType['icon'];
                                    $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                                @endphp
                                @if($ctIsEmoji)
                                    <span>{{ $ctIcon }}</span>
                                @else
                                    <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="h-3 w-3 text-blue-500" />
                                @endif
                            @endif
                            <span class="truncate">{{ $contentType['name_' . app()->getLocale()] ?? $contentType['name_en'] }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Author Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.author = !openSections.author"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm">{{ __('Author') }}</span>
                    @if(count($this->selectedAuthors) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-purple-500 text-white rounded-full">{{ count($this->selectedAuthors) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.author ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.author" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="authorSearchQuery"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 mb-2"
                    >
                    @if(count($this->authorSearchResults) > 0)
                    <div class="space-y-0.5 max-h-40 overflow-y-auto pr-1">
                        @foreach($this->authorSearchResults as $author)
                        <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.live="selectedAuthors"
                                value="{{ $author['id'] }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                            >
                            <span class="text-xs truncate">{{ $author['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                    @elseif($this->authorSearchQuery)
                    <p class="text-xs text-gray-500 dark:text-gray-400 py-1">{{ __('No results') }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Date Range Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.date = !openSections.date"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm">{{ __('Date Range') }}</span>
                    @if($this->dateFrom || $this->dateTo)
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.date ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.date" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 grid grid-cols-2 gap-2">
                    <div>
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                    </div>
                    <div>
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Theme Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.genre = !openSections.genre"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="text-sm">{{ __('Theme') }}</span>
                    @if(count($this->selectedGenres) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-yellow-500 text-white rounded-full">{{ count($this->selectedGenres) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.genre ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.genre" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2">
                    <input
                        type="text"
                        x-model="genreSearch"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 mb-2"
                    >
                    <div class="grid grid-cols-2 gap-1 max-h-48 overflow-y-auto pr-1">
                        @foreach($this->themes as $theme)
                        <label x-show="!genreSearch || '{{ $theme['theme'] }}'.toLowerCase().includes(genreSearch.toLowerCase())"
                               class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.live="selectedGenres"
                                value="{{ $theme['id_theme'] }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 flex-shrink-0"
                            >
                            <span class="truncate text-xs">{{ $theme['theme'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Genre Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.realGenre = !openSections.realGenre"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="text-sm">{{ __('Genre') }}</span>
                    @if(count($this->selectedRealGenres) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-orange-500 text-white rounded-full">{{ count($this->selectedRealGenres) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.realGenre ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.realGenre" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2">
                    <input
                        type="text"
                        x-model="realGenreSearch"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 mb-2"
                    >
                    <div class="grid grid-cols-2 gap-1 max-h-48 overflow-y-auto pr-1">
                        @foreach($this->genres as $genreItem)
                        <label x-show="!realGenreSearch || '{{ $genreItem['name'] }}'.toLowerCase().includes(realGenreSearch.toLowerCase())"
                               class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.live="selectedRealGenres"
                                value="{{ $genreItem['id'] }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 flex-shrink-0"
                            >
                            <span class="truncate text-xs">{{ $genreItem['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.section = !openSections.section"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="text-sm">{{ __('Section') }}</span>
                    @if(count($this->selectedSections) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-indigo-500 text-white rounded-full">{{ count($this->selectedSections) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.section ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.section" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2">
                    <input
                        type="text"
                        x-model="sectionSearch"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 mb-2"
                    >
                    <div class="grid grid-cols-1 gap-1 max-h-48 overflow-y-auto pr-1">
                        @foreach($this->sections as $section)
                        <label x-show="!sectionSearch || ('{{ $section['name'] }}'.toLowerCase().includes(sectionSearch.toLowerCase()) || '{{ $section['parent_name'] ?? '' }}'.toLowerCase().includes(sectionSearch.toLowerCase()))"
                               class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.live="selectedSections"
                                value="{{ $section['id'] }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 flex-shrink-0"
                            >
                            <span class="truncate text-xs">
                                @if($section['parent_name'])
                                    <span class="text-gray-400">{{ $section['parent_name'] }} / </span>
                                @endif
                                {{ $section['name'] }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Publisher Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.publisher = !openSections.publisher"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-sm">{{ __('Publisher') }}</span>
                    @if(count($this->selectedPublishers) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full">{{ count($this->selectedPublishers) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.publisher ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.publisher" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2">
                    <input
                        type="text"
                        x-model="publisherSearch"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 mb-2"
                    >
                    <div class="grid grid-cols-1 gap-1 max-h-48 overflow-y-auto pr-1">
                        @foreach($this->publishers as $publisher)
                        <label x-show="!publisherSearch || '{{ $publisher['name'] }}'.toLowerCase().includes(publisherSearch.toLowerCase())"
                               class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.live="selectedPublishers"
                                value="{{ $publisher['id'] }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 flex-shrink-0"
                            >
                            <span class="truncate text-xs">{{ $publisher['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Text Size Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.textSize = !openSections.textSize"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm">{{ __('Text Size') }}</span>
                    @if($this->textSizeRange !== [0, 500000])
                    <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.textSize ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.textSize" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2" x-data="{
                    min: @entangle('textSizeRange.0'),
                    max: @entangle('textSizeRange.1')
                }">
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span x-text="min.toLocaleString() + ' ' + '{{ __('words') }}'"></span>
                            <span x-text="max.toLocaleString() + ' ' + '{{ __('words') }}'"></span>
                        </div>
                        <div>
                            <input
                                type="range"
                                x-model="min"
                                min="0"
                                max="500000"
                                step="1000"
                                class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
                            >
                        </div>
                        <div>
                            <input
                                type="range"
                                x-model="max"
                                min="0"
                                max="500000"
                                step="1000"
                                class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alphabetical Sort Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.alphabetical = !openSections.alphabetical"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/>
                    </svg>
                    <span class="text-sm">{{ __('Alphabetical') }}</span>
                    @if($this->alphabeticalSort)
                    <span class="w-2 h-2 bg-cyan-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.alphabetical ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.alphabetical" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 space-y-0.5">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input
                            type="radio"
                            wire:model.live="alphabeticalSort"
                            value="asc"
                            class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                        >
                        <span class="text-xs">{{ __('A-Z') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input
                            type="radio"
                            wire:model.live="alphabeticalSort"
                            value="desc"
                            class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                        >
                        <span class="text-xs">{{ __('Z-A') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input
                            type="radio"
                            wire:model.live="alphabeticalSort"
                            value=""
                            class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5"
                        >
                        <span class="text-xs">{{ __('None') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Admin-Only Filters --}}
        @if(!$hideAdminFilters && auth()->check() && auth()->user()->role === 'admin')

        {{-- Extraction Status Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.extractionStatus = !openSections.extractionStatus"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">{{ __('Extraction Status') }}</span>
                    @if($this->statusFilter !== 'all')
                    <span class="w-2 h-2 bg-pink-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.extractionStatus ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.extractionStatus" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 space-y-0.5">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="all" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('All') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="pending" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">⏳ {{ __('Pending') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="processed" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">📋 {{ __('Ready for Review') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="confirmed" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">✅ {{ __('Confirmed') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="failed" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">❌ {{ __('Failed') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="statusFilter" value="rejected" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">🚫 {{ __('Rejected') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- File Format Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.format = !openSections.format"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm">{{ __('File Format') }}</span>
                    @if($this->formatFilter !== 'all')
                    <span class="w-2 h-2 bg-violet-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.format ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.format" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 grid grid-cols-2 gap-1">
                    @foreach(['all' => 'All', 'pdf' => 'PDF', 'epub' => 'EPUB', 'txt' => 'TXT', 'doc' => 'DOC', 'docx' => 'DOCX', 'fb2' => 'FB2', 'djvu' => 'DJVU', 'rtf' => 'RTF', 'mobi' => 'MOBI', 'azw' => 'AZW', 'azw3' => 'AZW3', 'lit' => 'LIT', 'html' => 'HTML'] as $value => $label)
                    <label class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="formatFilter" value="{{ $value }}" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Extraction Date Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.extractionDate = !openSections.extractionDate"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm">{{ __('Extraction Date') }}</span>
                    @if($this->dateFilter !== 'all')
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.extractionDate ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.extractionDate" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 space-y-0.5">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="dateFilter" value="all" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('All Time') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="dateFilter" value="1day" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Last 24h') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="dateFilter" value="7days" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Last 7 days') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="radio" wire:model.live="dateFilter" value="30days" class="border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Last 30 days') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Publication Status Filter --}}
        <div class="filter-section bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                @click="openSections.status = !openSections.status"
                class="w-full flex items-center justify-between text-start font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors px-3 py-2"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">{{ __('Publication Status') }}</span>
                    @if(count($this->publicationStatus) > 0)
                    <span class="px-1.5 py-0.5 text-xs bg-gray-500 text-white rounded-full">{{ count($this->publicationStatus) }}</span>
                    @endif
                </div>
                <svg
                    class="w-4 h-4 transition-transform duration-200 text-gray-400"
                    :class="openSections.status ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSections.status" x-collapse class="border-t border-gray-200 dark:border-gray-700">
                <div class="p-2 space-y-0.5">
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="checkbox" wire:model.live="publicationStatus" value="published" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Published') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="checkbox" wire:model.live="publicationStatus" value="hidden" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Hidden') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 py-1.5 px-2 rounded cursor-pointer transition-colors">
                        <input type="checkbox" wire:model.live="publicationStatus" value="pending" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                        <span class="text-xs">{{ __('Pending') }}</span>
                    </label>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
