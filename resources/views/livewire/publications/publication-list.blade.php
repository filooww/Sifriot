<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Publications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Bar -->
            <div class="mb-6 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search by title...') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                @auth
                    <button
                        wire:click="toggleDeleted"
                        class="px-4 py-2 {{ $showDeleted ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white rounded-lg transition"
                    >
                        {{ $showDeleted ? __('Show Active') : __('Show Deleted') }}
                    </button>
                    <a
                        href="#"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center"
                    >
                        {{ __('+ Add New') }}
                    </a>
                @endauth
                @guest
                    <a
                        href="{{ route('register') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center"
                    >
                        {{ __('Register to access full content') }}
                    </a>
                @endguest
            </div>

            @if($search)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Searching for: <span class="font-semibold">{{ $search }}</span>
                </p>
            @endif

            <!-- Filter and Publications Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Filter Sidebar -->
                <div class="lg:col-span-1">
                    @livewire('publications.publication-filters')
                </div>

                <!-- Publications Table -->
                <div class="lg:col-span-3">
                    @if(isset($resultCount))
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Showing {count} results', ['count' => $resultCount]) }}
                        </p>
                    @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Authors</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Publisher</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Year</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Upload Date</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($publications as $publication)
                                <tr class="{{ $publication->trashed() ? 'bg-red-50 dark:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $publication->id_publication }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $publication->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $publication->authorGroup->author_set ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $publication->publishing->publishing ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $publication->issue_year }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ $publication->issueType->issue_type ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $publication->upload_date?->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="{{ route('publications.show', $publication->id_publication) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">{{ __('View') }}</a>
                                            @auth
                                                <a href="#" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">{{ __('Edit') }}</a>
                                                @if($publication->trashed())
                                                    <button
                                                        wire:click="restorePublication({{ $publication->id_publication }})"
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                    >
                                                        {{ __('Restore') }}
                                                    </button>
                                                @else
                                                    <button
                                                        wire:click="deletePublication({{ $publication->id_publication }})"
                                                        wire:confirm="{{ __('Are you sure you want to delete this publication?') }}"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        {{ __('Delete') }}
                                                    </button>
                                                @endif
                                            @endauth
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            @if($search)
                                                <p class="text-lg font-medium">No publications found matching your search.</p>
                                                <p class="text-sm mt-2">Try adjusting your search terms.</p>
                                            @else
                                                <p class="text-lg font-medium">No publications found.</p>
                                                <p class="text-sm mt-2">Get started by adding a new publication.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $publications->links() }}
            </div>
                </div>
            </div>
        </div>
    </div>
</div>
