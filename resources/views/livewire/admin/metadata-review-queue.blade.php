<div class="space-y-6">
    <!-- Statistics Header -->
    <div class="grid grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">⏳ Pending</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">🔄 Processing</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">✅ Confirmed</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">❌ Failed</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['failed'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">🚫 Rejected</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="processed">Ready for Review</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="failed">Failed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Format Filter -->
            <div>
                <label for="formatFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Format
                </label>
                <select
                    id="formatFilter"
                    wire:model.live="formatFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">All</option>
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
                <label for="dateFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date Range
                </label>
                <select
                    id="dateFilter"
                    wire:model.live="dateFilter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">All Time</option>
                    <option value="1day">Last 24h</option>
                    <option value="7days">Last 7 days</option>
                    <option value="30days">Last 30 days</option>
                </select>
            </div>

            <!-- Sort -->
            <div>
                <label for="sortBy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Sort By
                </label>
                <select
                    id="sortBy"
                    wire:model.live="sortBy"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="created_at">Date (Newest)</option>
                    <option value="file_name">Filename (A-Z)</option>
                    <option value="status">Status</option>
                </select>
            </div>
        </div>

        <!-- Bulk Actions -->
        @if (count($selectedItems) > 0)
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg flex items-center justify-between">
                <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                    {{ count($selectedItems) }} item{{ count($selectedItems) !== 1 ? 's' : '' }} selected
                </span>
                <div class="flex gap-2">
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
    </div>

    <!-- File Metadata List -->
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
                            Filename
                            @if ($sortBy === 'file_name')
                                <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                        Format
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button
                            wire:click="sort('status')"
                            class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            Status
                            @if ($sortBy === 'status')
                                <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <button
                            wire:click="sort('created_at')"
                            class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            Date
                            @if ($sortBy === 'created_at')
                                <span class="inline-block">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($fileMetadataList as $metadata)
                    @php
                        $badgeInfo = $getStatusBadge($metadata->status);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
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
                                        {{ $metadata->getTitle() ? Str::limit($metadata->getTitle(), 50) : 'No title' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                {{ $getFileExtension($metadata->file_name) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $badgeInfo['color'] }}-100 dark:bg-{{ $badgeInfo['color'] }}-900/30 text-{{ $badgeInfo['color'] }}-800 dark:text-{{ $badgeInfo['color'] }}-200">
                                {{ $badgeInfo['icon'] }} {{ $badgeInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $metadata->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($metadata->status === 'processed')
                                    <button
                                        type="button"
                                        wire:click="$dispatch('open-review-modal', { metadataId: {{ $metadata->id }} })"
                                        class="px-3 py-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                    >
                                        Review
                                    </button>
                                @elseif ($metadata->status === 'failed')
                                    <button
                                        type="button"
                                        wire:click="reExtractSelected"
                                        class="px-3 py-1 text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded transition"
                                    >
                                        Retry
                                    </button>
                                @endif
                                <button
                                    type="button"
                                    wire:click="deleteMetadata({{ $metadata->id }})"
                                    onclick="confirm('Delete this metadata?') || event.stopImmediatePropagation()"
                                    class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <p class="text-gray-600 dark:text-gray-400">No metadata found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if ($fileMetadataList->hasPages())
            <div class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                {{ $fileMetadataList->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

    <!-- Review Modal/Drawer -->
    @if (isset($selectedMetadataId) && $selectedMetadataId)
        @php
            $selectedMetadata = \App\Models\FileMetadata::find($selectedMetadataId);
        @endphp

        @if ($selectedMetadata)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
                <div class="min-h-screen flex items-center justify-center p-4">
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
