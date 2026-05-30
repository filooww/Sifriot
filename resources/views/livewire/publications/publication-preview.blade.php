<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Breadcrumb Navigation -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <nav class="flex items-center text-xs text-gray-500 dark:text-gray-400">
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
        </div>
    </div>

    <!-- Hero Banner Section -->
    <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Status Badge & Type -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @if($publication->status === 'published')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Published') }}
                    </span>
                @endif
                @if($publication->contentType)
                    @php
                        $contentTypeBadge = $this->getContentTypeBadge();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $contentTypeBadge['color'] }}-100 dark:bg-{{ $contentTypeBadge['color'] }}-900/30 text-{{ $contentTypeBadge['color'] }}-800 dark:text-{{ $contentTypeBadge['color'] }}-200">
                        @if($contentTypeBadge['icon'])
                            @php
                                $ctIcon = $contentTypeBadge['icon'];
                                $ctIsEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $ctIcon);
                            @endphp
                            @if($ctIsEmoji)
                                <span class="text-xs">{{ $ctIcon }}</span>
                            @else
                                <x-dynamic-component :component="'heroicon-o-' . $ctIcon" class="w-3.5 h-3.5" />
                            @endif
                        @endif
                        {{ $publication->contentType->{'name_' . app()->getLocale()} ?? $publication->contentType->name_en }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    {{ $this->getFileExtension() }}
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    {{ $this->getFormattedFileSize() }}
                </span>
            </div>

            <!-- Title & Authors -->
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-3">
                    {{ $publication->title }}
                </h1>

                @if($publication->authors->count() > 0)
                    <div class="flex items-center gap-2 flex-wrap">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $publication->authors->pluck('author')->take(3)->join(', ') }}
                            @if($publication->authors->count() > 3)
                                <span class="text-gray-500">+{{ $publication->authors->count() - 3 }}</span>
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        @php
            $metadataSections = $this->getMetadataSections();
        @endphp

        <!-- Description Section (Full Width) -->
        @if(isset($metadataSections['description']))
            <div class="md:col-span-2 xl:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <x-heroicon-o-document-text class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['description']['title'] }}</h2>
                </div>
                <div id="description-container">
                    <p id="description-text" class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">
                        {{ $metadataSections['description']['content'] }}
                    </p>
                    @if(!$metadataSections['description']['isFullDescription'])
                    <button
                        x-data="{ expanded: false }"
                        @click="expanded = !expanded; $el.previousElementSibling.textContent = expanded ? '{{ $publication->description ?? '' }}' : '{{ $metadataSections['description']['content'] }}'"
                        class="mt-2 text-indigo-600 dark:text-indigo-400 text-xs font-medium hover:text-indigo-800 dark:hover:text-indigo-300 transition cursor-pointer"
                        x-text="expanded ? '{{ __('Show less') }} ↓' : '{{ __('Read more') }} →'"
                    ></button>
                    @endif
                </div>
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
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach ($publication->authors->take(8) as $author)
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                            {{ strtoupper(substr($author->author, 0, 1)) }}
                        </div>
                        <p class="text-gray-900 dark:text-white text-sm font-medium truncate">{{ $author->author }}</p>
                    </div>
                    @endforeach
                    @if($publication->authors->count() > 8)
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-1">
                            +{{ $publication->authors->count() - 8 }} {{ __('more') }}
                        </p>
                    @endif
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
                <div class="space-y-2">
                    @foreach($metadataSections['basic']['items'] as $item)
                    <div class="flex items-start gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/30">
                        <div class="w-6 h-6 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                            <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="w-3 h-3 text-gray-600 dark:text-gray-300" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ $item['label'] }}</p>
                            <p class="text-gray-900 dark:text-white text-sm font-medium truncate">{{ $item['value'] }}</p>
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
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($metadataSections['categories']['genres']['items']->take(6) as $genre)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 hover:bg-purple-200 dark:hover:bg-purple-900/50 transition cursor-default">
                                <span>📚</span>
                                {{ $genre->name_en ?? $genre->name_ru ?? $genre->name_he ?? 'Genre' }}
                            </span>
                        @endforeach
                        @if ($metadataSections['categories']['genres']['items']->count() > 6)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                +{{ $metadataSections['categories']['genres']['items']->count() - 6 }}
                            </span>
                        @endif
                    </div>
                </div>
                @endif

                @if($metadataSections['categories']['themes']->count() > 0)
                <div>
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Themes') }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($metadataSections['categories']['themes']->take(6) as $theme)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition cursor-default">
                                <span>🏷️</span>
                                <span class="truncate max-w-[120px]">{{ $theme->theme ?? 'Theme' }}</span>
                            </span>
                        @endforeach
                        @if($metadataSections['categories']['themes']->count() > 6)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                +{{ $metadataSections['categories']['themes']->count() - 6 }}
                            </span>
                        @endif
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
                <div class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto">
                    @foreach ($metadataSections['sections']['items'] as $section)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-900/50 transition cursor-default">
                            <span>📂</span>
                            <span class="truncate max-w-[100px]">{{ $section->{'name_' . app()->getLocale()} ?? $section->name_en }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Additional Information Section -->
            @if(isset($metadataSections['custom']))
            <div class="md:col-span-2 xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $metadataSections['custom']['title'] }}</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
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
            <div class="md:col-span-2 xl:col-span-3 bg-gray-900 rounded-xl shadow-lg p-6">
                @if ($isAuthenticated)
                    <!-- Authenticated User View -->
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto mb-3 bg-white/10 rounded-full flex items-center justify-center">
                            <x-heroicon-o-book-open class="w-6 h-6 text-white" />
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-white">{{ __('Ready to dive in?') }}</h3>
                        <p class="mb-4 text-gray-300 text-sm font-medium">{{ __('Access the full publication and download it for offline reading') }}</p>
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
                                        class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg inline-flex items-center justify-center gap-2 text-sm border-2 border-white/20"
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
                        <div class="w-12 h-12 mx-auto mb-3 bg-white/10 rounded-full flex items-center justify-center">
                            <x-heroicon-o-lock-closed class="w-6 h-6 text-white" />
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-white">{{ __('Unlock full access') }}</h3>
                        <p class="mb-4 text-gray-300 text-sm font-medium">{{ __('Create an account to view and download this publication') }}</p>
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
                <div class="md:col-span-2 xl:col-span-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
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