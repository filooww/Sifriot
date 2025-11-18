<div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-950">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
        <div class="px-6 py-4">
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
                <!-- Statistics Cards (Compact) -->
                <div class="space-y-2">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400">⏳ {{ __('Pending') }}</p>
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400">📋 {{ __('Ready for Review') }}</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['processed'] }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400">✅ {{ __('Confirmed') }}</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['confirmed'] }}</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400">❌ {{ __('Failed') }}</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400">🚫 {{ __('Rejected') }}</p>
                        <p class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['rejected'] }}</p>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4"></div>

                <!-- Filter Controls -->
                <div class="space-y-3">
                    <!-- Metadata Status Filter -->
                    <div>
                        <label for="statusFilter" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Metadata Status') }}
                        </label>
                        <select
                            id="statusFilter"
                            wire:model.live="statusFilter"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="all">{{ __('All') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="processed">{{ __('Ready for Review') }}</option>
                            <option value="confirmed">{{ __('Confirmed') }}</option>
                            <option value="failed">{{ __('Failed') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                        </select>
                    </div>

                    <!-- Format Filter -->
                    <div>
                        <label for="formatFilter" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Format') }}
                        </label>
                        <select
                            id="formatFilter"
                            wire:model.live="formatFilter"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="all">{{ __('All') }}</option>
                            <option value="pdf">PDF</option>
                            <option value="epub">EPUB</option>
                            <option value="txt">TXT</option>
                            <option value="doc">DOC</option>
                            <option value="docx">DOCX</option>
                            <option value="fb2">FB2</option>
                            <option value="djvu">DJVU</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label for="dateFilter" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Date Range') }}
                        </label>
                        <select
                            id="dateFilter"
                            wire:model.live="dateFilter"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="all">{{ __('All Time') }}</option>
                            <option value="1day">{{ __('Last 24h') }}</option>
                            <option value="7days">{{ __('Last 7 days') }}</option>
                            <option value="30days">{{ __('Last 30 days') }}</option>
                        </select>
                    </div>

                    <!-- Publication Status Filter -->
                    <div>
                        <label for="pubStatus" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Publication Status') }}
                        </label>
                        <select
                            id="pubStatus"
                            wire:model.live="filterPublicationStatus"
                            multiple
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="published">{{ __('Published') }}</option>
                            <option value="hidden">{{ __('Hidden') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
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

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4"></div>

                <!-- Publication Filters -->
                <div class="pb-4">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('Publication Filters') }}</h4>
                    @livewire('publications.publication-filters')
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6">
            <!-- Bulk Actions -->
    @if (count($selectedItems) > 0)
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg flex items-center justify-between">
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
            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
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
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($fileMetadataList as $metadata)
                            @php
                                $badgeInfo = $this->getStatusBadge($metadata->status);
                                $publication = $metadata->publication;
                                $pubStatusBadge = $publication ? $this->getPublicationStatusBadge($publication->status) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
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
                                            <span class="text-{{ $pubStatusBadge['color'] }}-600 dark:text-{{ $pubStatusBadge['color'] }}-400">{{ $pubStatusBadge['icon'] }}</span>
                                            <select
                                                wire:change="togglePublicationStatus({{ $publication->id_publication }}, $event.target.value)"
                                                class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 dark:bg-gray-800 dark:text-white"
                                            >
                                                <option value="pending" {{ $publication->status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                <option value="published" {{ $publication->status === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                                <option value="hidden" {{ $publication->status === 'hidden' ? 'selected' : '' }}>{{ __('Hidden') }}</option>
                                            </select>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('N/A') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $metadata->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($metadata->status === 'processed')
                                            <button
                                                type="button"
                                                wire:click="$set('selectedMetadataId', {{ $metadata->id }})"
                                                class="px-3 py-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                            >
                                                {{ __('Review') }}
                                            </button>
                                        @elseif ($metadata->status === 'failed')
                                            <button
                                                type="button"
                                                wire:click="reExtractSingle({{ $metadata->id }})"
                                                class="px-3 py-1 text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded transition"
                                            >
                                                {{ __('Retry') }}
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
</div>

<!-- Review Modal -->
    @if (isset($selectedMetadataId) && $selectedMetadataId)
        @php
            $selectedMetadata = \App\Models\FileMetadata::find($selectedMetadataId);
        @endphp

        @if ($selectedMetadata)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto" wire:click="$set('selectedMetadataId', null)">
                <div class="min-h-screen flex items-center justify-center p-4" wire:click.stop>
                    <div class="bg-white dark:bg-gray-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                        <!-- Modal Header -->
                        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Review Metadata
                            </h3>
                            <button
                                type="button"
                                wire:click="$set('selectedMetadataId', null)"
                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="p-6">
                            @livewire('admin.metadata-review-form', ['fileMetadata' => $selectedMetadata], key('metadata-review-' . $selectedMetadata->id))
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
