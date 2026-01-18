<div class="h-screen flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-900">
    <!-- Top Bar -->
    <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Left: Back Button -->
            <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('dashboard') : route('home') }}"
               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('Back') }}
            </a>

            <!-- Center: Title -->
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate max-w-md mx-4">
                {{ $publication->title }}
            </h1>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex overflow-hidden relative">
        <!-- Document Viewer (Full Width) -->
        <div class="flex-1 overflow-auto">
                    @auth
                        @if($publication->files->isNotEmpty())
                            <!-- Document Viewer Component -->
                            @livewire('publications.document-viewer', [
                                'publicationId' => $publication->id_publication,
                            ], key('viewer-' . ($selectedFileName ?? 'none')))

                            <!-- File Selection Tabs (if multiple files) -->
                            @if($publication->files->count() > 1)
                            <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                                <div class="flex flex-wrap gap-2 p-4 border-b border-gray-300 dark:border-gray-600">
                                    @foreach($publication->files as $file)
                                    <button
                                        wire:click="selectFile('{{ $file->file_name }}')"
                                        class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $selectedFileName === $file->file_name ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                    >
                                        {{ pathinfo($file->file_name, PATHINFO_FILENAME) }}
                                        <span class="text-xs ml-1 opacity-75">({{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) }})</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @else
                            <!-- No Files Message -->
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 p-8 rounded-lg text-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-600 dark:text-gray-400">{{ __('No files available for this publication') }}</p>
                            </div>
                        @endif
                    @endauth

            @guest
                <!-- Guest Login CTA -->
                <div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-800">
                    <div class="max-w-md p-8">
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg p-8">
                            <svg class="w-16 h-16 mx-auto mb-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h3 class="text-2xl font-bold text-center mb-4 text-gray-900 dark:text-gray-100">{{ __('View Documents Online') }}</h3>
                            <p class="text-center text-gray-600 dark:text-gray-300 mb-6">
                                {{ __('Sign in to view documents online without downloading') }}
                            </p>
                            <div class="flex gap-4 justify-center">
                                <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                                    {{ __('Login') }}
                                </a>
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition font-semibold">
                                    {{ __('Register') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endguest
        </div>

    </div>
</div>
