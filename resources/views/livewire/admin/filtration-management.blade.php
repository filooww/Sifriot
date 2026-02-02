<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('Filtration Management') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Manage all publication filter criteria in one place') }}
            </p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded" role="alert">
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded" role="alert">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                @php
                    $tabs = [
                        'content-types' => __('Content Types'),
                        'genres' => __('Genres'),
                        'themes' => __('Themes'),
                        'sections' => __('Sections'),
                        'authors' => __('Authors'),
                        'publishers' => __('Publishers'),
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tabLabel)
                    <button
                        wire:click="switchTab('{{ $tabKey }}')"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                            {{ $activeTab === $tabKey
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}"
                    >
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{-- Content Types Tab --}}
            @if($activeTab === 'content-types')
                @include('livewire.admin.filtration-tabs.content-types')
            @endif

            {{-- Genres Tab --}}
            @if($activeTab === 'genres')
                @include('livewire.admin.filtration-tabs.genres')
            @endif

            {{-- Themes Tab --}}
            @if($activeTab === 'themes')
                @include('livewire.admin.filtration-tabs.themes')
            @endif

            {{-- Sections Tab --}}
            @if($activeTab === 'sections')
                @include('livewire.admin.filtration-tabs.sections')
            @endif

            {{-- Authors Tab --}}
            @if($activeTab === 'authors')
                @include('livewire.admin.filtration-tabs.authors')
            @endif

            {{-- Publishers Tab --}}
            @if($activeTab === 'publishers')
                @include('livewire.admin.filtration-tabs.publishers')
            @endif
        </div>
    </div>

    {{-- Content Types Modal --}}
    @if($showContentTypeModal)
        @include('livewire.admin.filtration-modals.content-type-modal')
    @endif

    {{-- Genres Modal --}}
    @if($showGenreModal)
        @include('livewire.admin.filtration-modals.genre-modal')
    @endif

    {{-- Themes Modal --}}
    @if($showThemeModal)
        @include('livewire.admin.filtration-modals.theme-modal')
    @endif

    {{-- Sections Modal --}}
    @if($showSectionModal)
        @include('livewire.admin.filtration-modals.section-modal')
    @endif

    {{-- Authors Modal --}}
    @if($showAuthorModal)
        @include('livewire.admin.filtration-modals.author-modal')
    @endif

    {{-- Publishers Modal --}}
    @if($showPublisherModal)
        @include('livewire.admin.filtration-modals.publisher-modal')
    @endif
</div>
