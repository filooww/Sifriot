<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Publication Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('publications.index') }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                    ← {{ __('Back to Publications') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Title -->
                    <h1 class="text-3xl font-bold mb-4">{{ $publication->title }}</h1>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Authors -->
                        @if($publication->authorGroup)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Authors') }}</h3>
                            <p class="text-lg">{{ $publication->authorGroup->author_set }}</p>
                        </div>
                        @endif

                        <!-- Publisher -->
                        @if($publication->publishing)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Publisher') }}</h3>
                            <p class="text-lg">{{ $publication->publishing->publishing }}</p>
                        </div>
                        @endif

                        <!-- Year -->
                        @if($publication->issue_year)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Year') }}</h3>
                            <p class="text-lg">{{ $publication->issue_year }}</p>
                        </div>
                        @endif

                        <!-- Type -->
                        @if($publication->issueType)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Type') }}</h3>
                            <p class="text-lg">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                    {{ $publication->issueType->issue_type }}
                                </span>
                            </p>
                        </div>
                        @endif

                        <!-- Upload Date -->
                        @if($publication->upload_date)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Upload Date') }}</h3>
                            <p class="text-lg">{{ $publication->formatted_upload_date }}</p>
                        </div>
                        @endif

                        <!-- Magazine -->
                        @if($publication->magazine)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ __('Magazine') }}</h3>
                            <p class="text-lg">{{ $publication->magazine->magazine }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Description Section -->
                    @auth
                        @if($publication->add_char)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">{{ __('Description') }}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p>{{ $publication->add_char }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Files Section for Authenticated Users -->
                        @if($publication->files->isNotEmpty())
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">{{ __('Available Files') }}</h3>
                            <div class="space-y-3">
                                @foreach($publication->files as $file)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <p class="font-medium">{{ $file->file_name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $file->mime_type }} • {{ number_format($file->file_size_bytes / 1024, 2) }} KB
                                        </p>
                                    </div>
                                    <a
                                        href="{{ route('files.download', ['publication' => $file->id_publication, 'filename' => $file->file_name]) }}"
                                        class="ms-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                    >
                                        {{ __('Download') }}
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Themes -->
                        @if($publication->themes->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">{{ __('Themes') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($publication->themes as $theme)
                                <span class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-full text-sm">
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
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">{{ __('Description') }}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p>{{ Str::limit($publication->add_char, 200) }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Call to Action for Guests -->
                        <div class="mt-8 text-center p-8 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <svg class="w-16 h-16 mx-auto mb-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h3 class="text-2xl font-bold mb-4">{{ __('Register to access full content') }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                {{ __('This content is available to registered users only') }}
                            </p>
                            <div class="flex gap-4 justify-center">
                                <a
                                    href="{{ route('register') }}"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                                >
                                    {{ __('Create Account') }}
                                </a>
                                <a
                                    href="{{ route('login') }}"
                                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold"
                                >
                                    {{ __('Login') }}
                                </a>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
