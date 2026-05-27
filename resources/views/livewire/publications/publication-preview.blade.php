<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Header Section -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <nav class="flex items-center text-xs text-gray-500 dark:text-gray-400 mb-1">
                        <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                            {{ __('Home') }}
                        </a>
                        <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                            {{ __('Publications') }}
                        </a>
                        <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-medium">{{ __('Preview') }}</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $publication->title }}
                    </h1>
                </div>
                @if($publication->status === 'published')
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Published') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Column: Compact Cover Image Sidebar -->
            <div class="w-full lg:w-40 flex-shrink-0">
                <div class="sticky top-4 space-y-3">
                    <!-- Compact Cover Image Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden group">
                        @if ($coverImageUrl = $this->getCoverImageUrl())
                            <div class="relative aspect-[2/3] overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600">
                                <img
                                    src="{{ $coverImageUrl }}"
                                    alt="{{ $publication->title }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        @else
                            <div class="aspect-[2/3] bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 dark:from-indigo-900/30 dark:via-purple-900/30 dark:to-pink-900/30 flex items-center justify-center">
                                <div class="text-center px-2">
                                    <svg class="w-6 h-6 mx-auto text-indigo-300 dark:text-indigo-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <p class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400">{{ __('No cover') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Quick Actions Bar -->
                        <div class="px-2 py-1.5 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col gap-1">
                                @php
                                    $fileType = $this->getFileTypeIcon();
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px]">{{ $fileType['emoji'] }}</span>
                                    <p class="text-[9px] font-semibold text-gray-900 dark:text-white">{{ $this->getFileExtension() }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] text-gray-500 dark:text-gray-400">{{ __('Size') }}</span>
                                    <p class="text-[9px] font-semibold text-gray-900 dark:text-white">{{ $this->getFormattedFileSize() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Type Badge (Hidden on mobile for compactness) -->
                    @if($publication->contentType)
                    @php
                        $contentTypeBadge = $this->getContentTypeBadge();
                    @endphp
                    <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-1 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-1">
                            <div class="w-4 h-4 rounded bg-{{ $contentTypeBadge['color'] }}-100 dark:bg-{{ $contentTypeBadge['color'] }}-900/30 flex items-center justify-center">
                                @if($contentTypeBadge['icon'])
                                    @php
                                        $ctIcon = $contentTypeBadge['icon'];
                                        $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                                    @endphp
                                    @if($ctIsEmoji)
                                        <span class="text-[8px]">{{ $ctIcon }}</span>
                                    @else
                                        <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="w-2 h-2 text-{{ $contentTypeBadge['color'] }}-600 dark:text-{{ $contentTypeBadge['color'] }}-400" />
                                    @endif
                                @else
                                    <x-heroicon-o-document class="w-2 h-2 text-gray-600 dark:text-gray-400" />
                                @endif
                            </div>
                            <p class="text-[8px] font-semibold text-gray-900 dark:text-white truncate">
                                {{ $publication->contentType->{'name_' . app()->getLocale()} ?? $publication->contentType->name_en }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Compact Stats Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-1.5 border border-gray-100 dark:border-gray-700">
                        @php
                            $stats = $this->getPublicationStats();
                        @endphp
                        <div class="grid grid-cols-2 gap-1 text-center">
                            @if($stats['authors'] > 0)
                            <div class="bg-gray-50 dark:bg-gray-700/30 rounded p-1">
                                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['authors'] }}</p>
                                <p class="text-[8px] text-gray-500 dark:text-gray-400">A</p>
                            </div>
                            @endif
                            @if($stats['genres'] > 0)
                            <div class="bg-gray-50 dark:bg-gray-700/30 rounded p-1">
                                <p class="text-xs font-bold text-purple-600 dark:text-purple-400">{{ $stats['genres'] }}</p>
                                <p class="text-[8px] text-gray-500 dark:text-gray-400">G</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detailed Content -->
            <div class="flex-1 min-w-0 space-y-3">
                @php
                    $metadataSections = $this->getMetadataSections();
                @endphp

                <!-- Description Section (Priority) -->
                @if(isset($metadataSections['description']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <x-heroicon-o-document-text class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['description']['title'] }}</h2>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">
                        {{ $metadataSections['description']['content'] }}
                    </p>
                    @if(!$metadataSections['description']['isFullDescription'])
                    <button class="mt-2 text-indigo-600 dark:text-indigo-400 text-xs font-medium hover:text-indigo-800 dark:hover:text-indigo-300 transition">
                        {{ __('Read more') }} →
                    </button>
                    @endif
                </div>
                @endif

                <!-- Authors Section -->
                @if(isset($metadataSections['authors']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <x-heroicon-o-users class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['authors']['title'] }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $metadataSections['authors']['items']['authors']['count'] }} {{ __('author(s)') }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @foreach ($publication->authors as $index => $author)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-xs">
                                {{ strtoupper(substr($author->author, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-900 dark:text-white text-sm font-medium">{{ $author->author }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Publication Details Section -->
                @if(isset($metadataSections['basic']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-heroicon-o-book-open class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['basic']['title'] }}</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($metadataSections['basic']['items'] as $item)
                        <div class="flex items-start gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/30">
                            <div class="w-6 h-6 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                                <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="w-3 h-3 text-gray-600 dark:text-gray-300" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $item['label'] }}</p>
                                <p class="text-gray-900 dark:text-white text-sm font-medium">{{ $item['value'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Categories & Tags Section -->
                @if(isset($metadataSections['categories']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                            <x-heroicon-o-tag class="w-4 h-4 text-pink-600 dark:text-pink-400" />
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['categories']['title'] }}</h2>
                    </div>

                    @if($metadataSections['categories']['genres']['items']->count() > 0)
                    <div class="mb-3">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Genres') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($metadataSections['categories']['genres']['items'] as $genre)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 hover:bg-purple-200 dark:hover:bg-purple-900/50 transition cursor-default">
                                    <span>📚</span>
                                    {{ $genre->name_en ?? $genre->name_ru ?? $genre->name_he ?? 'Genre' }}
                                </span>
                            @endforeach
                            @if ($metadataSections['categories']['genres']['remaining'] > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    +{{ $metadataSections['categories']['genres']['remaining'] }} {{ __('more') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($metadataSections['categories']['themes']->count() > 0)
                    <div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Themes') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($metadataSections['categories']['themes'] as $theme)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition cursor-default">
                                    <span>🏷️</span>
                                    {{ $theme->theme ?? 'Theme' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Sections Section -->
                @if(isset($metadataSections['sections']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <x-heroicon-o-folder class="w-4 h-4 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['sections']['title'] }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $metadataSections['sections']['items']->count() }} {{ __('section(s)') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($metadataSections['sections']['items'] as $section)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-900/50 transition cursor-default">
                                <span>📂</span>
                                {{ $section->{'name_' . app()->getLocale()} ?? $section->name_en }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Additional Information Section -->
                @if(isset($metadataSections['custom']))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                            <x-heroicon-o-information-circle class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['custom']['title'] }}</h2>
                    </div>
                    <div class="space-y-2">
                        @foreach ($metadataSections['custom']['fields'] as $fieldData)
                            <x-custom-field-display
                                :field="$fieldData['field']"
                                :value="$fieldData['value']"
                            />
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- CTA Section -->
                <div class="bg-gradient-to-r from-indigo-700 to-purple-700 dark:from-indigo-800 dark:to-purple-800 rounded-xl shadow-lg p-6 text-white">
                    @if ($isAuthenticated)
                        <!-- Authenticated User View -->
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 bg-white/20 rounded-full flex items-center justify-center">
                                <x-heroicon-o-book-open class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-bold mb-2 text-white drop-shadow-md">{{ __('Ready to dive in?') }}</h3>
                            <p class="mb-4 text-white drop-shadow-sm text-sm font-medium">{{ __('Access the full publication and download it for offline reading') }}</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a
                                    href="{{ route('publications.show', $publication->id_publication) }}"
                                    class="px-6 py-3 bg-white text-gray-900 font-bold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg inline-flex items-center justify-center gap-2 text-sm"
                                >
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    {{ __('View Full Publication') }}
                                </a>

                                @if ($this->isPublished())
                                    @php
                                        $primaryFile = $this->getPrimaryFile();
                                    @endphp
                                    @if ($primaryFile)
                                        <a
                                            href="{{ route('files.download', [
                                                'publication' => $publication->id_publication,
                                                'filename' => $primaryFile->file_name
                                            ]) }}"
                                            class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg inline-flex items-center justify-center gap-2 text-sm border-2 border-white/30"
                                        >
                                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                            {{ __('Download') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Guest View -->
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 bg-white/20 rounded-full flex items-center justify-center">
                                <x-heroicon-o-lock-closed class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-bold mb-2 text-white drop-shadow-md">{{ __('Unlock full access') }}</h3>
                            <p class="mb-4 text-white drop-shadow-sm text-sm font-medium">{{ __('Create an account to view and download this publication') }}</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a
                                    href="{{ route('login') }}"
                                    class="px-6 py-3 bg-white text-gray-900 font-bold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg inline-flex items-center justify-center gap-2 text-sm"
                                >
                                    <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4" />
                                    {{ __('Login') }}
                                </a>
                                <a
                                    href="{{ route('register') }}"
                                    class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg inline-flex items-center justify-center gap-2 text-sm border-2 border-white/30"
                                >
                                    <x-heroicon-o-user-plus class="w-4 h-4" />
                                    {{ __('Sign Up Free') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Admin Section -->
                @if ($isAdmin)
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <x-heroicon-o-shield-check class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-amber-900 dark:text-amber-200">{{ __('Admin Controls') }}</p>
                                    <p class="text-xs text-amber-800 dark:text-amber-300">
                                        @if ($publication->status === 'published')
                                            {{ __('Visible to all users') }}
                                        @elseif ($publication->status === 'hidden')
                                            {{ __('Only visible to admins') }}
                                        @else
                                            {{ __('Not yet published') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $fileMetadata = $this->getFileMetadataForAdmin();
                                @endphp
                                @if($fileMetadata)
                                <a
                                    href="{{ route('admin.metadata-review', $fileMetadata->id) }}"
                                    class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition inline-flex items-center gap-1"
                                >
                                    <x-heroicon-o-cog-6-tooth class="w-3 h-3" />
                                    {{ __('Manage') }}
                                </a>
                                @else
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition inline-flex items-center gap-1"
                                >
                                    <x-heroicon-o-cog-6-tooth class="w-3 h-3" />
                                    {{ __('Manage') }}
                                </a>
                                @endif
                                <a
                                    href="{{ route('publications.show', $publication->id_publication) }}"
                                    class="px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-amber-700 dark:text-amber-300 text-sm font-medium rounded-lg transition inline-flex items-center gap-2 border border-amber-300 dark:border-amber-600"
                                >
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                    {{ __('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>