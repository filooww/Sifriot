<div class="py-12" x-data="{ filtersOpen: false }">
    <!-- Welcome Header (Full Width) -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Welcome to Sifriot Library') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Explore our collection') }}
            </p>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filters Button -->
        <div class="mb-4 flex justify-between items-center">
            <button
                @click="filtersOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>{{ __('Filters') }}</span>
            </button>
        </div>
                <!-- Search Input (Inline Filtering) -->
                <div class="mb-6">
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('Search publications by title or author...') }}"
                            class="w-full px-4 pe-10 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                        />

                        <!-- Clear Button (inside input on right) -->
                        @if($search)
                        <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                            <button
                                wire:click="$set('search', '')"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                                title="{{ __('Clear search') }}"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Active Search Indicator -->
                    @if($search)
                    <div class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>{{ __('Searching for') }}: <strong class="text-gray-900 dark:text-white">{{ $search }}</strong></span>
                        <button wire:click="$set('search', '')" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ __('Clear') }}
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Guest CTA Banner -->
                @guest
                <div class="mb-6 p-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                        {{ __('Register to access full content') }}
                    </h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                        {{ __('Create an account to download publications and access exclusive features.') }}
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('register') }}" wire:navigate
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                            {{ __('Register') }}
                        </a>
                        <a href="{{ route('login') }}" wire:navigate
                           class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 border border-blue-600 dark:border-blue-400 rounded-md transition">
                            {{ __('Log in') }}
                        </a>
                    </div>
                </div>
                @endguest

                <!-- Results Count -->
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Found') }} <span class="font-semibold">{{ $resultCount }}</span> {{ __('publications') }}
                </div>

                <!-- Publications Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @forelse($publications as $publication)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow flex flex-col h-full">
                            <!-- Cover Image -->
                            <div class="relative bg-gray-200 dark:bg-gray-700 aspect-[2/3] overflow-hidden flex items-center justify-center">
                                <a href="{{ route('publications.preview', $publication->id_publication) }}" wire:navigate class="w-full h-full">
                                    @if($publication->cover_image_path)
                                        <img
                                            src="{{ $publication->cover_image_path }}"
                                            alt="{{ $publication->title }}"
                                            class="w-full h-full object-cover hover:opacity-90 transition-opacity"
                                        />
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 flex flex-col items-center justify-center p-2">
                                            <svg class="w-8 h-8 text-gray-500 dark:text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.58 2 15.667c0 5.087 4.5 9.414 10 9.414s10-4.327 10-9.414c0-5.087-4.5-9.414-10-9.414z"></path>
                                            </svg>
                                            <span class="text-[10px] text-gray-600 dark:text-gray-400 text-center leading-tight">{{ __('No cover') }}</span>
                                        </div>
                                    @endif
                                </a>
                            </div>

                            <!-- Content -->
                            <div class="p-2 flex flex-col flex-grow">
                                <!-- Title -->
                                <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2 leading-tight">
                                    <a href="{{ route('publications.preview', $publication->id_publication) }}" wire:navigate
                                       class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $publication->title }}
                                    </a>
                                </h3>

                                <!-- Authors -->
                                @if($publication->authors->isNotEmpty())
                                <p class="text-[10px] text-gray-600 dark:text-gray-400 mb-1 line-clamp-1">
                                    {{ $publication->authors->pluck('author')->join(', ') }}
                                </p>
                                @endif

                                <!-- Content Type -->
                                @if($publication->contentType)
                                <div class="mt-auto pt-1">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded">
                                        @if($publication->contentType->icon)
                                            @php
                                                $ctIcon = $publication->contentType->icon;
                                                $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                                            @endphp
                                            @if($ctIsEmoji)
                                                <span class="text-[8px]">{{ $ctIcon }}</span>
                                            @else
                                                <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="h-2.5 w-2.5" />
                                            @endif
                                        @endif
                                        <span class="truncate max-w-full">{{ $publication->contentType->{'name_' . app()->getLocale()} ?? $publication->contentType->name_en }}</span>
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                            {{ __('No publications found') }}
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $publications->links() }}
                </div>
            </div>

            <!-- Filters Modal -->
            <div x-show="filtersOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @click.self="filtersOpen = false"
                 @keydown.escape.window="filtersOpen = false">

                <div x-show="filtersOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <h2 class="text-xl font-semibold text-white">{{ __('Filters') }}</h2>
                        </div>
                        <button @click="filtersOpen = false" class="text-white hover:text-blue-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body with Scrollable Filters -->
                    <div class="flex-1 overflow-y-auto p-6">
                        @livewire('publications.publication-filters', ['hideAdminFilters' => true, 'isModal' => true])
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end items-center gap-3">
                        <button @click="filtersOpen = false" class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition">
                            {{ __('Close') }}
                        </button>
                        <button @click="filtersOpen = false" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            {{ __('Apply Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
