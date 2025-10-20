<div class="py-12" x-data="{ sidebarOpen: true }">
    <!-- Admin Header & Statistics (Full Width) -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Admin Management') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Manage publications, users, and system settings') }}
            </p>
        </div>

        <!-- Quick Actions -->
        <div class="mb-6">
            <div class="flex gap-4">
                <a href="{{ route('admin.files.browse') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    {{ __('Browse Server Files') }}
                </a>
                <a href="{{ route('admin.files.register') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    {{ __('Upload New File') }}
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Publications') }}</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPublications) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Pending Review') }}</h3>
                <p class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($pendingCount) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Recent Uploads (7 days)') }}</h3>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($recentUploads) }}</p>
            </div>
        </div>
    </div>

    <!-- Main Layout: Sidebar + Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                @livewire('publications.publication-filters', ['hideAdminFilters' => false])
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 min-w-0">
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

                <!-- Admin Actions Bar -->
                <div class="mb-6 flex flex-wrap gap-3 items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <div class="flex gap-2">
                        <button wire:click="toggleDeleted"
                                class="px-4 py-2 {{ $showDeleted ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }} rounded-md transition">
                            {{ $showDeleted ? __('Hide Deleted') : __('Show Deleted') }}
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button disabled
                                class="px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed opacity-60"
                                title="{{ __('Publication creation form coming soon') }}">
                            {{ __('Add New Publication') }}
                        </button>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Found') }} <span class="font-semibold">{{ $resultCount }}</span> {{ __('publications') }}
                    @if($showDeleted)
                        <span class="text-red-600 dark:text-red-400">({{ __('Deleted Only') }})</span>
                    @endif
                </div>

                <!-- Publications Table (Admin View) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Title') }}
                                </th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Author') }}
                                </th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($publications as $publication)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('publications.show', $publication->id_publication) }}" wire:navigate
                                           class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $publication->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $publication->authors->pluck('author')->join(', ') ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $publication->upload_date?->format('Y-m-d') ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded
                                            {{ $publication->status === 'published' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : '' }}
                                            {{ $publication->status === 'pending' ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : '' }}
                                            {{ $publication->status === 'hidden' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' : '' }}">
                                            {{ ucfirst($publication->status ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-end text-sm">
                                        @if($showDeleted)
                                            <button wire:click="restorePublication({{ $publication->id_publication }})"
                                                    class="text-green-600 dark:text-green-400 hover:underline">
                                                {{ __('Restore') }}
                                            </button>
                                        @else
                                            <button wire:click="deletePublication({{ $publication->id_publication }})"
                                                    wire:confirm="{{ __('Are you sure you want to delete this publication?') }}"
                                                    class="text-red-600 dark:text-red-400 hover:underline">
                                                {{ __('Delete') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No publications found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $publications->links() }}
                </div>
            </main>
        </div>
    </div>
</div>
