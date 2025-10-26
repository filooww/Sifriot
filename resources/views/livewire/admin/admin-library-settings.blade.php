<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">{{ __('Library Paths') }}</h1>

        @if (session()->has('success'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Add New Path Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Add Library Path') }}</h2>

            <div class="mb-4">
                <label for="newPathInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Manual Path Input') }}
                </label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="newPathInput"
                        wire:model="newPathInput"
                        class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                        placeholder="/mnt/library/books"
                    >
                    <button
                        wire:click="openFolderPicker"
                        class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-md hover:bg-blue-700 dark:hover:bg-blue-800">
                        {{ __('Select Path') }}
                    </button>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Or select via folder picker') }}</p>
            </div>

            <button
                wire:click="addPath"
                class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 text-white rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-800">
                {{ __('Add Library Path') }}
            </button>
        </div>

        <!-- Existing Paths Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Path') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Path Status') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Last Verified') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($libraryPaths as $path)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $path->path }}
                                @if($path->label)
                                    <span class="text-gray-500 dark:text-gray-400">({{ $path->label }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($path->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                        {{ __('Error') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $path->last_verified_at?->diffForHumans() ?? __('Never') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button
                                    wire:click="verifyPath({{ $path->id }})"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 me-3">
                                    {{ __('Verify Path') }}
                                </button>
                                <button
                                    wire:click="removePath({{ $path->id }})"
                                    wire:confirm="{{ __('Are you sure you want to remove this path?') }}"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    {{ __('Remove Path') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                {{ __('No library paths configured yet') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Folder Picker Modal -->
        @if($showFolderPicker)
            <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeFolderPicker">
                <div class="relative top-20 mx-auto p-5 border border-gray-300 dark:border-gray-700 w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Select Folder') }}</h3>
                        <button wire:click="closeFolderPicker" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>
                    <div class="overflow-y-auto max-h-96">
                        @livewire('admin.folder-browser', ['selectionMode' => true])
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
