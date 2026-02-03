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
                        !empty($filterSections) || !empty($filterAuthors) || !empty($filterGenres) ||
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

            <!-- Status Filter Buttons (Horizontal) -->
            <div class="mt-4 flex flex-wrap gap-2">
                <button wire:click="$set('statusFilter', 'all')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'all' ? 'bg-gray-600 text-white ring-2 ring-gray-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    📊 {{ __('All') }} ({{ $stats['pending'] + $stats['processed'] + $stats['confirmed'] + $stats['failed'] + $stats['rejected'] }})
                </button>
                <button wire:click="$set('statusFilter', 'pending')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'pending' ? 'bg-yellow-600 text-white ring-2 ring-yellow-400' : 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/40' }}">
                    ⏳ {{ __('Pending') }} ({{ $stats['pending'] }})
                </button>
                <button wire:click="$set('statusFilter', 'processed')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'processed' ? 'bg-blue-600 text-white ring-2 ring-blue-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40' }}">
                    📋 {{ __('Ready for Review') }} ({{ $stats['processed'] }})
                </button>
                <button wire:click="$set('statusFilter', 'confirmed')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'confirmed' ? 'bg-green-600 text-white ring-2 ring-green-400' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40' }}">
                    ✅ {{ __('Confirmed') }} ({{ $stats['confirmed'] }})
                </button>
                <button wire:click="$set('statusFilter', 'failed')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'failed' ? 'bg-red-600 text-white ring-2 ring-red-400' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40' }}">
                    ❌ {{ __('Failed') }} ({{ $stats['failed'] }})
                </button>
                <button wire:click="$set('statusFilter', 'rejected')" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $statusFilter === 'rejected' ? 'bg-gray-600 text-white ring-2 ring-gray-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    🚫 {{ __('Rejected') }} ({{ $stats['rejected'] }})
                </button>

                @if ($orphanedCount > 0)
                    <button wire:click="toggleOrphanedView" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $showOrphanedPublications ? 'bg-orange-600 text-white ring-2 ring-orange-400' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/40' }}">
                        ⚠️ {{ __('Orphaned') }} ({{ $orphanedCount }})
                    </button>
                @endif
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

            <!-- Orphaned Publications Panel -->
            @if ($showOrphanedPublications && $orphanedPublications->count() > 0)
                <div class="mt-4 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200">
                            ⚠️ {{ __('Orphaned Publications') }} ({{ $orphanedPublications->count() }})
                        </h3>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="deleteAllOrphaned"
                                wire:confirm="{{ __('Delete all orphaned publications? This cannot be undone.') }}"
                                class="px-3 py-1 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition"
                            >
                                🗑️ {{ __('Delete All') }}
                            </button>
                            <button
                                type="button"
                                wire:click="toggleOrphanedView"
                                class="px-3 py-1 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition"
                            >
                                ✕ {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-orange-700 dark:text-orange-300 mb-3">
                        {{ __('These publications have no associated files and were likely created due to upload errors.') }}
                    </p>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach ($orphanedPublications as $pub)
                            <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded border border-orange-200 dark:border-orange-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($pub->title, 60) }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">ID: {{ $pub->id_publication }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $pub->created_at->format('M d, Y') }}</span>
                                </div>
                                <button
                                    type="button"
                                    wire:click="deleteOrphanedPublication({{ $pub->id_publication }})"
                                    wire:confirm="{{ __('Delete this publication?') }}"
                                    class="px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                >
                                    🗑️ {{ __('Delete') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content with Sidebar -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Filters -->
        <aside class="bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 overflow-y-auto transition-all duration-300 {{ $sidebarCollapsed ? 'w-12' : 'w-64' }}">
            <!-- Collapse Toggle Button -->
            <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                <button
                    wire:click="$toggle('sidebarCollapsed')"
                    class="w-full p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition flex items-center justify-center"
                    title="{{ $sidebarCollapsed ? __('Expand Filters') : __('Collapse Filters') }}"
                >
                    @if($sidebarCollapsed)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    @endif
                </button>
            </div>

            @if(!$sidebarCollapsed)
            <div class="p-4 space-y-4">
                <!-- Divider -->

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
                    @livewire('publications.publication-filters', ['hideAdminFilters' => true])
                </div>
            </div>
            @endif
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
                    ✅ {{ __('Confirm All') }}
                </button>
                <button
                    type="button"
                    wire:click="rejectAllSelected"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition"
                >
                    🚫 {{ __('Reject All') }}
                </button>
                <button
                    type="button"
                    wire:click="reExtractSelected"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
                >
                    🔄 {{ __('Re-extract') }}
                </button>
                <div x-data="{
                    showTooltip: false,
                    showConfirmModal: false,
                    confirmedCount: 0
                }"
                    x-on:confirm-ai-extraction.window="showConfirmModal = true; confirmedCount = $event.detail.confirmedCount"
                    class="relative"
                >
                    <button
                        type="button"
                        wire:click="extractWithAISelected"
                        @if (!$geminiConfigured)
                            disabled
                            @mouseenter="showTooltip = true"
                            @mouseleave="showTooltip = false"
                        @endif
                        class="px-4 py-2 text-sm font-medium rounded-lg transition flex items-center gap-2
                            @if ($geminiConfigured)
                                bg-purple-600 hover:bg-purple-700 text-white
                            @else
                                bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed
                            @endif
                        "
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="extractWithAISelected"
                    >
                        <span wire:loading.remove wire:target="extractWithAISelected">✨</span>
                        <span wire:loading wire:target="extractWithAISelected">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="extractWithAISelected">{{ __('Extract with AI') }}</span>
                        <span wire:loading wire:target="extractWithAISelected">{{ __('Extracting...') }}</span>
                    </button>
                    @if (!$geminiConfigured)
                        <div
                            x-show="showTooltip"
                            x-transition
                            class="absolute left-0 top-full mt-2 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg shadow-lg whitespace-nowrap z-50"
                        >
                            {{ __('Gemini API not configured') }}
                        </div>
                    @endif



                    <!-- Confirmation Modal for Overwriting Confirmed Items -->
                    <div
                        x-show="showConfirmModal"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                        @click.self="showConfirmModal = false"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ __('Overwrite Confirmed Items?') }}
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('Your selection includes :count confirmed item(s). AI extraction will overwrite their existing metadata. Do you want to continue?', ['count' => '<span x-text="confirmedCount" class="font-semibold text-yellow-600 dark:text-yellow-400"></span>']) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showConfirmModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button
                                    type="button"
                                    @click="showConfirmModal = false; $wire.extractWithAISelected(true)"
                                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition"
                                >
                                    {{ __('Yes, Extract with AI') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        @if ($metadata->status === 'processed' || $metadata->status === 'rejected' || $metadata->status === 'pending')
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
                                            onclick="confirm('{{ __('Delete this publication?') }}') || event.stopImmediatePropagation()"
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
