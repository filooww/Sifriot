<div class="w-full" wire:poll.2s="refreshProgress">
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Scan Form -->
    @if (!$currentScanJob || in_array($currentScanJob->status, ['completed', 'cancelled', 'failed']))
        <div class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ __('Scan Options') }}</h2>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="folderPath">
                    {{ __('Folder Path') }}
                </label>
                <input
                    type="text"
                    wire:model="folderPath"
                    id="folderPath"
                    class="shadow appearance-none border border-gray-300 dark:border-gray-600 rounded w-full py-2 px-3 text-gray-700 dark:text-gray-100 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="content/books"
                >
                @error('folderPath') <span class="text-red-500 dark:text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="recursive" class="me-2 rounded dark:bg-gray-700 dark:border-gray-600">
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ __('Recursive') }}</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                    {{ __('File Format Filters') }}
                </label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="pdf" class="me-1 rounded dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-900 dark:text-gray-100">PDF</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="epub" class="me-1 rounded dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-900 dark:text-gray-100">EPUB</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="txt" class="me-1 rounded dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-900 dark:text-gray-100">TXT</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="docx" class="me-1 rounded dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-900 dark:text-gray-100">DOCX</span>
                    </label>
                </div>
            </div>

            <div>
                <button
                    wire:click="startScan"
                    class="bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('Start Scan') }}
                </button>
            </div>
        </div>
    @endif

    <!-- Scan Progress -->
    @if ($currentScanJob && in_array($currentScanJob->status, ['pending', 'processing', 'paused']))
        <div class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ __('Scan Progress') }}</h2>

            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-700 dark:text-gray-300">{{ __('Processing') }}: {{ $currentScanJob->files_registered + $currentScanJob->files_skipped + $currentScanJob->files_failed }}/{{ $currentScanJob->total_files_found }} {{ __('files') }}</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ number_format($currentScanJob->progress_percent, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div
                        class="bg-blue-600 dark:bg-blue-500 h-4 rounded-full transition-all duration-300"
                        style="width: {{ $currentScanJob->progress_percent }}%"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-green-100 dark:bg-green-900 p-4 rounded">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Success') }}</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentScanJob->files_registered }}</div>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Skipped') }}</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentScanJob->files_skipped }}</div>
                </div>
                <div class="bg-red-100 dark:bg-red-900 p-4 rounded">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Failed') }}</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentScanJob->files_failed }}</div>
                </div>
            </div>

            <div class="flex gap-2">
                @if ($currentScanJob->status === 'processing')
                    <button
                        wire:click="pauseScan"
                        class="bg-yellow-500 dark:bg-yellow-600 hover:bg-yellow-700 dark:hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    >
                        {{ __('Pause Scan') }}
                    </button>
                @endif

                <button
                    wire:click="cancelScan"
                    class="bg-red-500 dark:bg-red-600 hover:bg-red-700 dark:hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('Cancel Scan') }}
                </button>

                <a
                    href="{{ route('admin.scan-results', ['scanJobId' => $currentScanJob->id]) }}"
                    class="bg-gray-500 dark:bg-gray-600 hover:bg-gray-700 dark:hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('View Results') }}
                </a>
            </div>
        </div>
    @endif
</div>
