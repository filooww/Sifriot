<div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-950">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
        <div class="px-4 py-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Metadata Review Dashboard') }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage and review file metadata extraction') }}</p>
                </div>
                <div class="flex gap-2">
                    @if ($search || $statusFilter !== 'all' || $formatFilter !== 'all' || $dateFilter !== 'all' ||
                        !empty($filterCategories) || !empty($filterAuthors) || !empty($filterGenres) ||
                        !empty($filterPublicationStatus) || $filterDateFrom || $filterDateTo ||
                        $filterTextSizeRange !== [0, 500000] || $filterAlphabeticalSort)
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-medium rounded-lg transition"
                        >
                            ✕ {{ __('Clear Filters') }}
                        </button>
                    @endif
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-4">
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="{{ __('Search by filename or title...') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                />
            </div>
        </div>
    </div>

    <!-- Main Content with Sidebar -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Filters -->
        <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
            <div class="p-4 space-y-4">
                <!-- Statistics Cards (Compact) - Clickable filters -->
                <div class="space-y-2">
                    <button wire:click="$set('statusFilter', 'pending')" class="w-full text-left bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition {{ $statusFilter === 'pending' ? 'ring-2 ring-yellow-600' : '' }}">
                        <p class="text-xs text-gray-600 dark:text-gray-400">⏳ {{ __('Pending') }}</p>
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</p>
                    </button>
                    <button wire:click="$set('statusFilter', 'processed')" class="w-full text-left bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition {{ $statusFilter === 'processed' ? 'ring-2 ring-blue-600' : '' }}">
                        <p class="text-xs text-gray-600 dark:text-gray-400">📋 {{ __('Ready for Review') }}</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['processed'] }}</p>
                    </button>
                    <button wire:click="$set('statusFilter', 'confirmed')" class="w-full text-left bg-green-50 dark:bg-green-900/20 rounded-lg p-3 hover:bg-green-100 dark:hover:bg-green-900/40 transition {{ $statusFilter === 'confirmed' ? 'ring-2 ring-green-600' : '' }}">
                        <p class="text-xs text-gray-600 dark:text-gray-400">✅ {{ __('Confirmed') }}</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['confirmed'] }}</p>
                    </button>
                    <button wire:click="$set('statusFilter', 'failed')" class="w-full text-left bg-red-50 dark:bg-red-900/20 rounded-lg p-3 hover:bg-red-100 dark:hover:bg-red-900/40 transition {{ $statusFilter === 'failed' ? 'ring-2 ring-red-600' : '' }}">
                        <p class="text-xs text-gray-600 dark:text-gray-400">❌ {{ __('Failed') }}</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</p>
                    </button>
                    <button wire:click="$set('statusFilter', 'rejected')" class="w-full text-left bg-gray-100 dark:bg-gray-800 rounded-lg p-3 hover:bg-gray-200 dark:hover:bg-gray-700 transition {{ $statusFilter === 'rejected' ? 'ring-2 ring-gray-600' : '' }}">
                        <p class="text-xs text-gray-600 dark:text-gray-400">🚫 {{ __('Rejected') }}</p>
                        <p class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['rejected'] }}</p>
                    </button>
                    <button wire:click="$set('statusFilter', 'all')" class="w-full text-left bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ $statusFilter === 'all' ? 'ring-2 ring-gray-400' : '' }} mt-2">
                        <p class="text-xs text-gray-600 dark:text-gray-400">📊 {{ __('All') }}</p>
                        <p class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['pending'] + $stats['processed'] + $stats['confirmed'] + $stats['failed'] + $stats['rejected'] }}</p>
                    </button>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4"></div>

                <!-- Table Sort Controls -->
                <div class="space-y-3 mb-4">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('Table Sort') }}</h4>

                    <!-- Sort -->
                    <div>
                        <label for="sortBy" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Sort By') }}
                        </label>
                        <select
                            id="sortBy"
                            wire:model.live="sortBy"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="created_at">{{ __('Date') }}</option>
                            <option value="file_name">{{ __('Filename') }}</option>
                            <option value="status">{{ __('Status') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="sortDir" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Direction') }}
                        </label>
                        <select
                            id="sortDir"
                            wire:model.live="sortDirection"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="desc">{{ __('Descending') }}</option>
                            <option value="asc">{{ __('Ascending') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Publication & Metadata Filters Component -->
                <div class="pb-4">
                    @livewire('publications.publication-filters', ['hideAdminFilters' => false])
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Bulk Actions -->
    @if (count($selectedItems) > 0)
        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg flex items-center justify-between">
            <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                {{ count($selectedItems) }} item{{ count($selectedItems) !== 1 ? 's' : '' }} selected
            </span>
            <div class="flex gap-2 flex-wrap">
                <button
                    type="button"
                    wire:click="confirmAllSelected"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition"
                >
                    ✅ Confirm All
                </button>
                <button
                    type="button"
                    wire:click="rejectAllSelected"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition"
                >
                    🚫 Reject All
                </button>
                <button
                    type="button"
                    wire:click="reExtractSelected"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
                >
                    🔄 Re-extract
                </button>
            </div>
        </div>
    @endif

            <!-- File Metadata Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col min-h-96 flex-1 dark:bg-gray-900">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectAll"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300"
                                />
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button
                                    wire:click="sort('file_name')"
                                    class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                >
                                    {{ __('Filename') }}
                                    @if ($sortBy === 'file_name')
                                        <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('Format') }}
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button
                                    wire:click="sort('status')"
                                    class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                >
                                    {{ __('Metadata Status') }}
                                    @if ($sortBy === 'status')
                                        <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('Publication Status') }}
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button
                                    wire:click="sort('created_at')"
                                    class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                >
                                    {{ __('Date') }}
                                    @if ($sortBy === 'created_at')
                                        <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 flex-1">
                        @forelse ($fileMetadataList as $metadata)
                            @php
                                $badgeInfo = $this->getStatusBadge($metadata->status);
                                $publication = $metadata->publication;
                                $pubStatusBadge = $publication ? $this->getPublicationStatusBadge($publication->status) : null;
                            @endphp
                            <tr wire:key="metadata-{{ $metadata->id }}" class="hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <td class="px-6 py-3">
                                    <input
                                        type="checkbox"
                                        wire:model.live="selectedItems"
                                        value="{{ $metadata->id }}"
                                        class="w-4 h-4 text-blue-600 rounded border-gray-300"
                                    />
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($metadata->file_name, 40) }}</span>
                                        @if ($metadata->status === 'processed' || $metadata->status === 'confirmed')
                                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $metadata->getTitle() ? Str::limit($metadata->getTitle(), 50) : __('No title') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        {{ $this->getFileExtension($metadata->file_name) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $badgeInfo['color'] }}-100 dark:bg-{{ $badgeInfo['color'] }}-900/30 text-{{ $badgeInfo['color'] }}-800 dark:text-{{ $badgeInfo['color'] }}-200">
                                        {{ $badgeInfo['icon'] }} {{ $badgeInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if ($publication)
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ $pubStatusBadge['icon'] }}</span>
                                            <select
                                                wire:change="togglePublicationStatus({{ $publication->id_publication }}, $event.target.value)"
                                                class="text-sm font-medium border-2 border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 dark:bg-gray-800 dark:text-white bg-white text-gray-900 hover:border-blue-400 dark:hover:border-blue-500 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            >
                                                <option value="pending" {{ $publication->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                <option value="published" {{ $publication->status === 'published' ? 'selected' : '' }}>🌐 Published</option>
                                                <option value="hidden" {{ $publication->status === 'hidden' ? 'selected' : '' }}>🔒 Hidden</option>
                                            </select>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400 px-3 py-1.5 inline-block bg-gray-100 dark:bg-gray-800 rounded-lg">{{ __('No Publication') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $metadata->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2 items-center">
                                        @if ($metadata->status === 'processed' || $metadata->status === 'rejected')
                                            <button
                                                type="button"
                                                wire:click="openReview({{ (int)$metadata->id }})"
                                                class="px-3 py-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                            >
                                                {{ __('Review') }}
                                            </button>
                                        @elseif ($metadata->status === 'confirmed')
                                            <button
                                                type="button"
                                                wire:click="openReview({{ (int)$metadata->id }})"
                                                class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition"
                                            >
                                                {{ __('Edit') }}
                                            </button>
                                        @elseif ($metadata->status === 'failed')
                                            <button
                                                type="button"
                                                wire:click="reExtractSingle({{ (int)$metadata->id }})"
                                                wire:loading.attr="disabled"
                                                wire:loading.delay.longer
                                                wire:target="reExtractSingle({{ (int)$metadata->id }})"
                                                class="px-3 py-1 text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <span wire:loading.remove wire:target="reExtractSingle({{ (int)$metadata->id }})">{{ __('Retry') }}</span>
                                                <span wire:loading wire:target="reExtractSingle({{ (int)$metadata->id }})">{{ __('Processing...') }}</span>
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="deleteMetadata({{ $metadata->id }})"
                                            onclick="confirm('{{ __('Delete this metadata?') }}') || event.stopImmediatePropagation()"
                                            class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <p class="text-gray-600 dark:text-gray-400">{{ __('No metadata found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($fileMetadataList->hasPages())
                    <div class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                        {{ $fileMetadataList->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Review Modal -->
    @if (isset($selectedMetadataId) && $selectedMetadataId)
        @php
            $selectedMetadata = \App\Models\FileMetadata::find($selectedMetadataId);
        @endphp

        @if ($selectedMetadata)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto flex items-center justify-center p-4" wire:click="closeReview">
                <div class="bg-white dark:bg-gray-900 rounded-xl max-w-3xl w-full max-h-[95vh] overflow-hidden shadow-2xl" wire:click.stop>
                    <!-- Modal Header with Status -->
                    <div class="sticky top-0 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border-b border-gray-200 dark:border-gray-600 px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    @if ($selectedMetadata->status === 'confirmed')
                                        ✏️ {{ __('Edit Metadata') }}
                                    @else
                                        📋 {{ __('Review Metadata') }}
                                    @endif
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ Str::limit($selectedMetadata->file_name, 50) }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeReview"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 p-2 rounded-lg transition"
                            title="Close (Esc)"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Content with Scrollable Form -->
                    <div class="overflow-y-auto" style="max-height: calc(95vh - 100px);">
                        <div class="p-6 md:p-8">
                            @livewire('admin.metadata-review-form', ['fileMetadata' => $selectedMetadata], key('metadata-review-' . $selectedMetadata->id))
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
