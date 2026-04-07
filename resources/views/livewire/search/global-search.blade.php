<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Search Input -->
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="searchQuery"
            @focus="open = true"
            @input="open = true"
            placeholder="{{ __('Search publications') }}"
            class="w-full sm:w-80 px-4 py-2 ps-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        />

        <!-- Search Icon -->
        <svg class="absolute start-3 top-2.5 h-5 w-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>

        <!-- Clear Button -->
        @if(!empty($searchQuery))
        <button
            wire:click="clearSearch"
            @click="open = false"
            class="absolute end-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        @endif
    </div>

    <!-- Search Results Dropdown -->
    @if($showResults && !empty($searchQuery))
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 w-full sm:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto"
    >
        @if($hasResults)
            <!-- Results List -->
            <div class="py-2">
                @foreach($results as $publication)
                <a
                    href="{{ route('publications.show', $publication) }}"
                    wire:navigate
                    @click="open = false"
                    class="block px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {!! $this->highlightSearchTerms($publication->title, $searchQuery) !!}
                    </div>
                    @if($publication->authors->first())
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ __('Author') }}: {{ $publication->authors->first()?->author ?? __('Unknown') }}
                    </div>
                    @endif
                    @if($publication->publishers->count() > 0)
                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        {{ $publication->publishers->first()->name_en ?? '' }}
                    </div>
                    @endif
                </a>
                @endforeach
            </div>

            <!-- View All Results Link -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                <a
                    href="{{ route('home', ['search' => $searchQuery]) }}"
                    wire:navigate
                    @click="open = false"
                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                >
                    {{ __('View all results') }} ({{ $results->count() }}+)
                </a>
            </div>
        @else
            <!-- No Results -->
            <div class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm">{{ __('No results found for') }} "<strong>{{ $searchQuery }}</strong>"</p>
                <p class="mt-1 text-xs">{{ __('Try different keywords or check your spelling') }}</p>
            </div>
        @endif
    </div>
    @endif
</div>
