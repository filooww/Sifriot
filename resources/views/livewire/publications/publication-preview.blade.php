<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Content -->
    <div class="max-w-6xl mx-auto px-4 py-8 sm:py-12">
        <!-- Header: Publication Title -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $publication->title }}
            </h1>
            @if ($publication->issue_year)
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Published in') }} {{ $publication->issue_year }}
                </p>
            @endif
        </div>

        <!-- Main Content: Two-column Layout (Desktop) / Stacked (Mobile) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Left Column: Cover Image -->
            <div class="md:col-span-1">
                <div class="sticky top-4">
                    <!-- Cover Image -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-6">
                        @if ($coverImageUrl = $this->getCoverImageUrl())
                            <img
                                src="{{ $coverImageUrl }}"
                                alt="{{ $publication->title }}"
                                class="w-full aspect-[3/4] object-cover"
                            />
                        @else
                            <div class="w-full aspect-[3/4] bg-gray-300 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- File Format Badge -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Format') }}</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                                📄 {{ $this->getFileExtension() }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium">{{ __('Size:') }}</span> {{ $this->getFormattedFileSize() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Metadata -->
            <div class="md:col-span-2 space-y-6">
                <!-- Authors -->
                @if ($publication->authors->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Authors') }}</h3>
                        <div class="space-y-2">
                            @foreach ($publication->authors as $author)
                                <div class="text-gray-700 dark:text-gray-300">
                                    {{ $author->author }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Publisher & Year -->
                @if ($publication->publishing || $publication->issue_year)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="grid grid-cols-2 gap-4">
                            @if ($publication->publishing)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Publisher') }}</h4>
                                    <p class="text-gray-900 dark:text-white">{{ $publication->publishing->publishing }}</p>
                                </div>
                            @endif
                            @if ($publication->issue_year)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Year') }}</h4>
                                    <p class="text-gray-900 dark:text-white">{{ $publication->issue_year }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Description -->
                @if ($publication->description)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Description') }}</h3>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $this->getTruncatedDescription() }}
                        </p>
                    </div>
                @endif

                <!-- Genres & Themes -->
                @php
                    $genreData = $this->getDisplayGenres();
                @endphp
                @if ($genreData['genres']->count() > 0 || $publication->themeSet)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Categories') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($genreData['genres'] as $genre)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200">
                                    {{ $genre->name_en ?? $genre->name_ru ?? $genre->name_he ?? 'Genre' }}
                                </span>
                            @endforeach

                            @if ($genreData['remaining'] > 0)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    +{{ $genreData['remaining'] }} {{ __('more') }}
                                </span>
                            @endif

                            @if ($publication->themeSet)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200">
                                    🏷️ {{ $publication->themeSet->theme_set_en ?? 'Theme' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- CTA Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 rounded-lg shadow-lg p-8 text-white">
                    @if ($isAuthenticated)
                        <!-- Authenticated User View -->
                        <div class="text-center">
                            <h3 class="text-2xl font-bold mb-4">{{ __('Ready to read?') }}</h3>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a
                                    href="{{ route('publications.show', $publication->id_publication) }}"
                                    class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition inline-block text-center"
                                >
                                    👁️ {{ __('View Full Publication') }}
                                </a>

                                @if ($this->isPublished())
                                    <button
                                        onclick="alert('Download coming soon!')"
                                        class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition"
                                    >
                                        ⬇️ {{ __('Download') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Guest View -->
                        <div class="text-center">
                            <h3 class="text-2xl font-bold mb-2">{{ __('Register to access full content') }}</h3>
                            <p class="mb-6 text-blue-100">{{ __('Sign in or create an account to view and download this publication') }}</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a
                                    href="{{ route('login') }}"
                                    class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition inline-block text-center"
                                >
                                    🔐 {{ __('Login') }}
                                </a>
                                <a
                                    href="{{ route('register') }}"
                                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition inline-block text-center"
                                >
                                    📝 {{ __('Sign Up') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Publication Status Badge (Admin Only) -->
        @if ($isAdmin)
            <div class="mb-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-900 dark:text-yellow-200">{{ __('Admin: Publication Status') }}</p>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                            @if ($publication->status === 'published')
                                🌐 {{ __('Published') }} - {{ __('Visible to all users') }}
                            @elseif ($publication->status === 'hidden')
                                🔒 {{ __('Hidden') }} - {{ __('Only visible to admins') }}
                            @else
                                ⏳ {{ __('Pending') }} - {{ __('Not yet published') }}
                            @endif
                        </p>
                    </div>
                    <a
                        href="{{ route('dashboard') }}"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition"
                    >
                        {{ __('Manage') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
