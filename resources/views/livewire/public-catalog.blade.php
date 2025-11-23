<div class="py-12" x-data="{ sidebarOpen: true }">
    <!-- Welcome Header (Full Width) -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Welcome to Seferium Library') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Explore our collection') }}
            </p>
        </div>
    </div>

    <!-- Main Layout: Sidebar + Content -->
    <div class="flex gap-6">
        <!-- Left Sidebar: Filters (Collapsible) -->
        <aside
            x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="w-64 flex-shrink-0"
        >
            @livewire('publications.publication-filters', ['hideAdminFilters' => true])
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Toggle Sidebar Button -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="mb-4 inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span x-text="sidebarOpen ? '{{ __('Hide Filters') }}' : '{{ __('Show Filters') }}'"></span>
                </button>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                                        <div class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 flex flex-col items-center justify-center p-4">
                                            <svg class="w-16 h-16 text-gray-500 dark:text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.58 2 15.667c0 5.087 4.5 9.414 10 9.414s10-4.327 10-9.414c0-5.087-4.5-9.414-10-9.414z"></path>
                                            </svg>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 text-center">{{ __('No cover image') }}</span>
                                        </div>
                                    @endif
                                </a>
                            </div>

                            <!-- Content -->
                            <div class="p-4 flex flex-col flex-grow">
                                <!-- Title -->
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                    <a href="{{ route('publications.preview', $publication->id_publication) }}" wire:navigate
                                       class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $publication->title }}
                                    </a>
                                </h3>

                                <!-- Authors -->
                                @if($publication->authors->isNotEmpty())
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                    {{ $publication->authors->pluck('author')->join(', ') }}
                                </p>
                                @else
                                <p class="text-sm text-gray-500 dark:text-gray-500 mb-3 italic">{{ __('No author') }}</p>
                                @endif

                                <!-- Publisher (optional) -->
                                @if($publication->publishing)
                                <p class="text-xs text-gray-500 dark:text-gray-500 mb-2 line-clamp-1">
                                    {{ $publication->publishing->publishing }}
                                </p>
                                @endif

                                <!-- Categories -->
                                @if($publication->categories->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($publication->categories->take(2) as $category)
                                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                                        {{ $category->name }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                <!-- Metadata Footer -->
                                <div class="mt-auto pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $publication->upload_date?->format('Y-m-d') }}</span>
                                    @if($publication->word_count)
                                    <span>{{ number_format($publication->word_count) }} {{ __('words') }}</span>
                                    @endif
                                </div>
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
        </main>
        </div>
    </div>
</div>
