<div class="px-4 py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                @if ($fileMetadata->status === 'confirmed')
                    ✏️ {{ __('Edit Metadata') }}
                @else
                    📋 {{ __('Review Metadata') }}
                @endif
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ Str::limit($fileMetadata->file_name, 50) }}
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">
            &larr; {{ __('Back to Dashboard') }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        @livewire('admin.metadata-review-form', ['fileMetadata' => $fileMetadata], key('metadata-review-' . $fileMetadata->id))
    </div>
</div>
