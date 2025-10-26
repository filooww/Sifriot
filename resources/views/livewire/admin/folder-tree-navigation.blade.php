<div class="bg-white dark:bg-gray-800 shadow-md rounded p-4 h-full">
    <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">{{ __('Folder Navigation') }}</h3>

    @if (empty($rootPaths))
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">{{ __('No configured paths available') }}</p>
            <a href="{{ route('admin.settings.library-paths') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                {{ __('Configure library paths in settings') }}
            </a>
        </div>
    @else
        <div class="space-y-2 max-h-screen overflow-y-auto">
            @foreach ($rootPaths as $root)
                <div class="space-y-1">
                    <!-- Root Path Item -->
                    <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer group"
                         @click="$wire.selectFolder('{{ $root['path'] }}')"
                         :class="{ 'bg-blue-100 dark:bg-blue-900': $wire.selectedPath === '{{ $root['path'] }}' }">

                        <!-- Expand/Collapse Button -->
                        <button wire:click.stop="@if(in_array($root['path'], $expandedPaths))collapseFolder@else expandFolder@endif('{{ $root['path'] }}')"
                                class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            @if (in_array($root['path'], $expandedPaths))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </button>

                        <!-- Folder Icon -->
                        <svg class="w-5 h-5 text-yellow-500 dark:text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        </svg>

                        <!-- Root Path Label -->
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex-1 truncate">
                            {{ $root['label'] ?? $root['path'] }}
                        </span>
                    </div>

                    <!-- Child Folders (if expanded) -->
                    @if (in_array($root['path'], $expandedPaths) && isset($childFolders[$root['path']]))
                        <div class="ps-6 space-y-1 border-l border-gray-300 dark:border-gray-600">
                            @foreach ($childFolders[$root['path']] as $child)
                                <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                     @click="$wire.selectFolder('{{ $child['path'] }}')"
                                     :class="{ 'bg-blue-100 dark:bg-blue-900': $wire.selectedPath === '{{ $child['path'] }}' }">

                                    <!-- Folder Icon (no expand button for depth 1) -->
                                    <svg class="w-5 h-5 text-yellow-500 dark:text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                    </svg>

                                    <!-- Child Folder Name -->
                                    <span class="text-sm text-gray-700 dark:text-gray-300 flex-1 truncate">
                                        {{ $child['name'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
