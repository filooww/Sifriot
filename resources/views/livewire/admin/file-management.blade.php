<div>
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

    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-8">
            <button
                wire:click="setActiveTab('browse')"
                class="px-4 py-2 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'browse' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}"
            >
                📁 {{ __('Browse & Register Files') }}
            </button>

            <button
                wire:click="setActiveTab('upload')"
                class="px-4 py-2 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'upload' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}"
            >
                📤 {{ __('Upload New File') }}
            </button>

            <button
                wire:click="setActiveTab('settings')"
                class="px-4 py-2 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'settings' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}"
            >
                ⚙️ {{ __('Settings') }}
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="mt-6">
        <!-- Browse & Register Tab with Integrated Bulk Scan -->
        @if ($activeTab === 'browse')
            <div class="space-y-6">
                <!-- File Browser Section -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">{{ __('Browse Server Files') }}</h2>
                    @livewire('admin.folder-browser', ['key' => 'folder-browser-' . time()])
                </div>

                <!-- Bulk Scan Section (Integrated) -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">{{ __('Bulk Folder Scan') }}</h2>
                    @livewire('admin.bulk-folder-scanner', ['key' => 'bulk-folder-scanner-' . time()])
                </div>

                <!-- Scan Results Section (Inline) -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6" id="scan-results-section">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">{{ __('Scan Results') }}</h2>
                    @livewire('admin.scan-results-viewer', ['scanJobId' => $currentScanJobId, 'key' => 'scan-results-' . time()])
                </div>
            </div>
        @endif

        <!-- Upload Tab -->
        @if ($activeTab === 'upload')
            <div class="bg-white dark:bg-gray-800 shadow-md rounded p-6">
                @livewire('admin.file-registration-form', ['filePath' => $selectedFilePath, 'key' => 'file-registration-form-' . time()])
            </div>
        @endif

        <!-- Settings Tab -->
        @if ($activeTab === 'settings')
            <div>
                @livewire('admin.admin-library-settings', ['key' => 'admin-library-settings-' . time()])
            </div>
        @endif
    </div>
</div>
