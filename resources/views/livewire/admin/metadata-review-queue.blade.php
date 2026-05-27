<div class="space-y-6" wire:poll.5s="{{ $queueStats['is_processing'] ? '' : 'keep-alive' }}">

    <!-- Processing Progress Banner -->
    @if ($queueStats['is_processing'])
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="animate-spin h-5 w-5 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                        Processing Metadata Extraction
                    </span>
                </div>
                <span class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                    {{ $queueStats['completed'] }} / {{ $queueStats['total'] }} ({{ $queueStats['percent_complete'] }}%)
                </span>
            </div>
            <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2.5">
                <div class="bg-blue-600 dark:bg-blue-500 h-2.5 rounded-full transition-all duration-300" style="width: {{ $queueStats['percent_complete'] }}%"></div>
            </div>
            <div class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                <span class="font-medium">{{ $queueStats['pending'] }}</span> pending,
                <span class="font-medium">{{ $queueStats['processing'] }}</span> processing
            </div>
        </div>
    @endif

    <!-- Statistics Header -->
    <div class="grid grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">⏳ Pending</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">🔄 Processing</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">📋 Processed</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['processed'] }}</p>
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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h2>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Last updated: {{ now()->format('H:i:s') }}
                </span>
                <button
                    type="button"
                    wire:click="$refresh"
                    class="px-3 py-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                    title="Refresh now"
                >
                    🔄 Refresh
                </button>
            </div>
        </div>
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
                    <button
                        type="button"
                        wire:click="generateCoversForSelected"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition"
                    >
                        🖼️ Generate Covers
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
                        $badgeInfo = $this->getStatusBadge($metadata->status);
                        $isProcessing = in_array($metadata->status, ['pending', 'processing']);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $isProcessing ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                        <td class="px-6 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectedItems"
                                value="{{ $metadata->id }}"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300"
                            />
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                @if ($isProcessing)
                                    <div class="animate-pulse h-2 w-2 bg-blue-500 rounded-full"></div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($metadata->file_name, 40) }}</span>
                                    @if ($metadata->status === 'processed' || $metadata->status === 'confirmed')
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $metadata->getTitle() ? Str::limit($metadata->getTitle(), 50) : 'No title' }}
                                        </span>
                                    @endif
                                </div>
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
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $metadata->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($metadata->status === 'processed')
                                    <a
                                        href="{{ route('admin.metadata-review', $metadata->id) }}"
                                        class="px-3 py-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                    >
                                        Review
                                    </a>
                                @elseif ($metadata->status === 'failed')
                                    <button
                                        type="button"
                                        wire:click="reExtractSingle({{ $metadata->id }})"
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
                {{ $fileMetadataList->links() }}
            </div>
        @endif
    </div>

</div>
