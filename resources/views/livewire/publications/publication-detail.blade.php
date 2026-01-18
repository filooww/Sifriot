<div>
    <div
        x-data="{
            isFullscreen: false,
            showHeader: true,
            showInfo: false,
            headerTimeout: null,
            init() {
                this.resetHeaderTimer();

                document.addEventListener('mousemove', (e) => {
                    if (e.clientY < 60) {
                        this.showHeader = true;
                        this.resetHeaderTimer();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                    if (e.key === 'f' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                        e.preventDefault();
                        this.toggleFullscreen();
                    }
                    if (e.key === 'i' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                        e.preventDefault();
                        this.showInfo = !this.showInfo;
                    }
                    if (e.key === 'Escape') {
                        if (this.showInfo) {
                            this.showInfo = false;
                        } else if (this.isFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                    if (!this.isFullscreen) {
                        this.showHeader = true;
                    }
                });
            },
            resetHeaderTimer() {
                clearTimeout(this.headerTimeout);
                this.showHeader = true;
                if (this.isFullscreen) {
                    this.headerTimeout = setTimeout(() => {
                        if (!this.showInfo) {
                            this.showHeader = false;
                        }
                    }, 2500);
                }
            },
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    this.$refs.readerContainer.requestFullscreen().catch(err => {
                        console.log('Fullscreen not supported');
                    });
                } else {
                    document.exitFullscreen();
                }
            }
        }"
        x-ref="readerContainer"
        class="h-screen flex flex-col overflow-hidden bg-gray-100 dark:bg-gray-950"
        :class="{ 'cursor-none': isFullscreen && !showHeader }"
        @mousemove="resetHeaderTimer()"
    >
        <!-- Floating Header - Auto-hides in fullscreen -->
        <div
            x-show="showHeader || !isFullscreen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-full"
            class="flex-shrink-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-white/10 px-4 py-2 z-50"
            :class="{ 'absolute top-0 left-0 right-0': isFullscreen }"
        >
            <div class="flex items-center justify-between">
                <!-- Left: Back Button -->
                <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('dashboard') : route('home') }}"
                   class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 transition-colors group p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
                    <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline text-sm">{{ __('Back') }}</span>
                </a>

                <!-- Center: Title (minimal) -->
                <div class="flex-1 mx-4 text-center overflow-hidden" x-show="!showInfo">
                    <h1 class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                        {{ $publication->title }}
                    </h1>
                </div>

                <!-- Right: Controls -->
                <div class="flex items-center gap-1">
                    @auth
                        <!-- Info Toggle -->
                        <button
                            @click="showInfo = !showInfo"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                            :class="{ 'bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white': showInfo }"
                            title="{{ __('Publication Info') }} (I)"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>

                        <!-- Download Button -->
                        @if($publication->files->isNotEmpty())
                        @php
                            $currentFile = $publication->files->firstWhere('file_name', $selectedFileName) ?? $publication->files->first();
                            $encodedFilename = rtrim(strtr(base64_encode($currentFile->file_name), '+/', '-_'), '=');
                        @endphp
                        <a
                            href="{{ route('files.download', ['publication' => $publication->id_publication, 'filename' => $encodedFilename]) }}"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                            title="{{ __('Download') }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </a>
                        @endif
                    @endauth

                    <!-- Fullscreen Toggle -->
                    <button
                        @click="toggleFullscreen()"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                        :title="isFullscreen ? '{{ __('Exit Fullscreen') }} (F)' : '{{ __('Fullscreen') }} (F)'"
                    >
                        <svg x-show="!isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                        <svg x-show="isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex overflow-hidden relative">

            <!-- Publication Info Panel (Slides from left) -->
            @auth
            <div
                x-show="showInfo"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="absolute left-0 top-0 bottom-0 w-80 sm:w-96 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-white/10 z-40 flex flex-col"
                @click.away="showInfo = false"
            >
                <!-- Info Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-white/5">
                    <h3 class="font-medium text-gray-900 dark:text-white">{{ __('Publication Info') }}</h3>
                    <button @click="showInfo = false" class="p-1.5 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Info Content -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Title -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">{{ $publication->title }}</h2>
                    </div>

                    <!-- Authors -->
                    @if($publication->authors->isNotEmpty())
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Authors') }}</span>
                        <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $publication->authors->pluck('author_name')->join(', ') }}</p>
                    </div>
                    @endif

                    <!-- Publisher -->
                    @if($publication->publishing)
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Publisher') }}</span>
                        <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $publication->publishing->publishing_name }}</p>
                    </div>
                    @endif

                    <!-- Year -->
                    @if($publication->year)
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Year') }}</span>
                        <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $publication->year }}</p>
                    </div>
                    @endif

                    <!-- Themes -->
                    @if($publication->themes->isNotEmpty())
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Themes') }}</span>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($publication->themes as $theme)
                            <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 rounded-md border border-gray-200 dark:border-white/10">
                                {{ $theme->theme_name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Description -->
                    @if($publication->description)
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500">{{ __('Description') }}</span>
                        <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm leading-relaxed">{{ $publication->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endauth

            <!-- Document Viewer (Full Width) -->
            <div class="flex-1 overflow-hidden">
                @auth
                    @if($publication->files->isNotEmpty())
                        <!-- Document Viewer Component -->
                        @livewire('publications.document-viewer', [
                            'publicationId' => $publication->id_publication,
                        ], key('viewer-' . ($selectedFileName ?? 'none')))
                    @else
                        <!-- No Files Message -->
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center p-8">
                                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('No files available') }}</h3>
                                <p class="text-gray-500">{{ __('This publication has no files to display') }}</p>
                            </div>
                        </div>
                    @endif
                @endauth

                @guest
                    <!-- Guest Login CTA - Immersive Design -->
                    <div class="flex items-center justify-center h-full bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
                        <div class="max-w-lg w-full mx-4">
                            <div class="relative">
                                <!-- Decorative elements -->
                                <div class="absolute -top-20 -left-20 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl"></div>
                                <div class="absolute -bottom-20 -right-20 w-40 h-40 bg-purple-500/10 rounded-full blur-3xl"></div>

                                <div class="relative bg-white dark:bg-gray-800/50 backdrop-blur-xl rounded-2xl border border-gray-200 dark:border-gray-700/50 p-8 shadow-2xl">
                                    <!-- Icon -->
                                    <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>

                                    <!-- Content -->
                                    <h3 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-3">
                                        {{ __('Read Online') }}
                                    </h3>
                                    <p class="text-center text-gray-600 dark:text-gray-400 mb-8">
                                        {{ __('Sign in to read documents directly in your browser with our immersive reader') }}
                                    </p>

                                    <!-- Features -->
                                    <div class="grid grid-cols-2 gap-4 mb-8">
                                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-sm">
                                            <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Multiple formats') }}
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-sm">
                                            <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Fullscreen mode') }}
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-sm">
                                            <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('No downloads') }}
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-sm">
                                            <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Any device') }}
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <a href="{{ route('login') }}"
                                           class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-semibold text-center shadow-lg shadow-blue-500/25">
                                            {{ __('Sign In') }}
                                        </a>
                                        <a href="{{ route('register') }}"
                                           class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition font-semibold text-center border border-gray-200 dark:border-gray-600/50">
                                            {{ __('Create Account') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endguest
            </div>
        </div>

        <!-- Keyboard shortcuts hint (shows briefly) -->
        <div
            x-data="{ showHint: true }"
            x-init="setTimeout(() => showHint = false, 4000)"
            x-show="showHint"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 text-xs px-4 py-2.5 rounded-full border border-gray-200 dark:border-white/10 shadow-lg z-50 flex items-center gap-3"
        >
            <span class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/10 rounded text-[10px] font-mono font-medium">F</kbd>
                <span class="text-gray-500 dark:text-gray-400">{{ __('fullscreen') }}</span>
            </span>
            <span class="w-px h-3 bg-gray-200 dark:bg-white/20"></span>
            <span class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/10 rounded text-[10px] font-mono font-medium">I</kbd>
                <span class="text-gray-500 dark:text-gray-400">{{ __('info') }}</span>
            </span>
            <span class="w-px h-3 bg-gray-200 dark:bg-white/20"></span>
            <span class="flex items-center gap-1.5">
                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/10 rounded text-[10px] font-mono font-medium">Esc</kbd>
                <span class="text-gray-500 dark:text-gray-400">{{ __('close') }}</span>
            </span>
        </div>
    </div>
</div>
