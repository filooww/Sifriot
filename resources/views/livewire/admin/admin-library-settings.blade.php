<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ __('Library Settings') }}</h1>

        @if (session()->has('success'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Library Path Configuration -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Library Path') }}</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Configured Library Path') }}
                </label>
                <div class="flex items-center gap-2">
                    <div class="flex-1 px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        {{ $libraryPath }}
                    </div>
                    <span class="px-3 py-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        {{ __('Active') }}
                    </span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('This path is configured via the LIBRARY_PATH environment variable. To change it, update your .env file and restart the application.') }}
                </p>
            </div>
        </div>

        <!-- Cleanup Section -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 shadow rounded-lg p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4 text-red-900 dark:text-red-100">{{ __('Danger Zone') }}</h2>

            <div class="mb-4">
                <p class="text-red-800 dark:text-red-200 mb-4">
                    {{ __('Delete all publications from the database. This action cannot be undone.') }}
                </p>
                <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                    {{ __('Warning: This will permanently delete:') }}
                </p>
                <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside mb-4 space-y-1">
                    <li>{{ __('All publications') }}</li>
                    <li>{{ __('All file references') }}</li>
                    <li>{{ __('All registration logs') }}</li>
                </ul>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-3 p-2 bg-yellow-50 dark:bg-yellow-900/30 rounded">
                    ℹ️ {{ __('The actual files on disk will remain untouched.') }}
                </p>
            </div>

            <button
                wire:click="cleanupAllPublications"
                wire:confirm="{{ __('Are you absolutely sure? This will permanently delete ALL publications and cannot be undone!') }}"
                class="px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded-md hover:bg-red-700 dark:hover:bg-red-800 font-semibold">
                🗑️ {{ __('Delete All Publications') }}
            </button>
        </div>

    </div>
</div>
