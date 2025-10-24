<div class="p-6" wire:poll.2s="refreshProgress">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4">{{ __('Bulk Folder Scan') }}</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>

    @if (!$currentScanJob || in_array($currentScanJob->status, ['completed', 'cancelled', 'failed']))
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="folderPath">
                    {{ __('Folder Path') }}
                </label>
                <input
                    type="text"
                    wire:model="folderPath"
                    id="folderPath"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="content/books"
                >
                @error('folderPath') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="recursive" class="me-2">
                    <span class="text-sm">{{ __('Recursive') }}</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    {{ __('File Format Filters') }}
                </label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="pdf" class="me-1">
                        <span class="text-sm">PDF</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="epub" class="me-1">
                        <span class="text-sm">EPUB</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="txt" class="me-1">
                        <span class="text-sm">TXT</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="fileFormatFilters" value="docx" class="me-1">
                        <span class="text-sm">DOCX</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="maxDepth">
                    {{ __('Max Depth') }} ({{ __('optional') }})
                </label>
                <input
                    type="number"
                    wire:model="maxDepth"
                    id="maxDepth"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="5"
                >
            </div>

            <div>
                <button
                    wire:click="startScan"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('Start Scan') }}
                </button>
            </div>
        </div>
    @endif

    @if ($currentScanJob && in_array($currentScanJob->status, ['pending', 'processing', 'paused']))
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h3 class="text-xl font-bold mb-4">{{ __('Scan Progress') }}</h3>

            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <span>{{ __('Processing') }}: {{ $currentScanJob->files_registered + $currentScanJob->files_skipped + $currentScanJob->files_failed }}/{{ $currentScanJob->total_files_found }} {{ __('files') }}</span>
                    <span>{{ number_format($currentScanJob->progress_percent, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div
                        class="bg-blue-600 h-4 rounded-full transition-all duration-300"
                        style="width: {{ $currentScanJob->progress_percent }}%"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-green-100 p-4 rounded">
                    <div class="text-sm text-gray-600">{{ __('Success') }}</div>
                    <div class="text-2xl font-bold">{{ $currentScanJob->files_registered }}</div>
                </div>
                <div class="bg-yellow-100 p-4 rounded">
                    <div class="text-sm text-gray-600">{{ __('Skipped') }}</div>
                    <div class="text-2xl font-bold">{{ $currentScanJob->files_skipped }}</div>
                </div>
                <div class="bg-red-100 p-4 rounded">
                    <div class="text-sm text-gray-600">{{ __('Failed') }}</div>
                    <div class="text-2xl font-bold">{{ $currentScanJob->files_failed }}</div>
                </div>
            </div>

            <div class="flex gap-2">
                @if ($currentScanJob->status === 'processing')
                    <button
                        wire:click="pauseScan"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    >
                        {{ __('Pause Scan') }}
                    </button>
                @endif

                <button
                    wire:click="cancelScan"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('Cancel Scan') }}
                </button>

                <a
                    href="{{ route('admin.scan-results', ['scanJobId' => $currentScanJob->id]) }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    {{ __('View Results') }}
                </a>
            </div>
        </div>
    @endif
</div>
