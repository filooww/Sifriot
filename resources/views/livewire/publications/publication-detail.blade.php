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

            <!-- Right: Info Toggle Button -->
            <button
                @click="$wire.set('showMetadata', !$wire.showMetadata)"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Info') }}
            </button>
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

        <!-- Sliding Metadata Panel -->
        <div
            x-data="{ show: @entangle('showMetadata') }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute right-0 top-0 h-full w-full md:w-96 bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto z-10"
            style="display: none;"
        >
            <!-- Close Button -->
            <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold">{{ __('Publication Info') }}</h2>
                <button
                    @click="show = false"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Title -->
                <h1 class="text-xl font-bold mb-4">{{ $publication->title }}</h1>

                            <!-- Metadata Grid -->
                            <div class="space-y-4">
                                <!-- Authors -->
                                @if($publication->authorGroup)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Authors') }}</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $publication->authorGroup->author_set }}</p>
                                </div>
                                @endif

                                <!-- Publisher -->
                                @if($publication->publishing)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Publisher') }}</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $publication->publishing->publishing }}</p>
                                </div>
                                @endif

                                <!-- Year -->
                                @if($publication->issue_year)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Year') }}</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $publication->issue_year }}</p>
                                </div>
                                @endif

                                <!-- Type -->
                                @if($publication->issueType)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Type') }}</h3>
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        {{ $publication->issueType->issue_type }}
                                    </span>
                                </div>
                                @endif

                                <!-- Upload Date -->
                                @if($publication->upload_date)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Upload Date') }}</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $publication->formatted_upload_date }}</p>
                                </div>
                                @endif

                                <!-- Magazine -->
                                @if($publication->magazine)
                                <div>
                                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Magazine') }}</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $publication->magazine->magazine }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- Divider -->
                            <hr class="my-6 border-gray-300 dark:border-gray-600">

                            <!-- Description Section -->
                            @auth
                                @if($publication->add_char)
                                <div class="mb-6">
                                    <h3 class="text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ __('Description') }}</h3>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ Str::limit($publication->add_char, 300) }}
                                    </p>
                                </div>
                                @endif

                                <!-- Themes -->
                                @if($publication->themes->isNotEmpty())
                                <div>
                                    <h3 class="text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ __('Themes') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($publication->themes as $theme)
                                        <span class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs">
                                            {{ $theme->theme }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endauth

                            @guest
                                <!-- Limited Description for Guests -->
                                @if($publication->add_char)
                                <div>
                                    <h3 class="text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ __('Description') }}</h3>
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        {{ Str::limit($publication->add_char, 200) }}
                                    </p>
                                </div>
                                @endif
                @endguest
            </div>
        </div>
    </div>
</div>
