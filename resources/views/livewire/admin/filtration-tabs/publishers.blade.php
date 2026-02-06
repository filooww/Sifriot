<div class="p-6">
    {{-- Create Button --}}
    <div class="mb-6">
        <button wire:click="createPublisher"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <x-heroicon-o-plus class="h-5 w-5" />
            {{ __('Create Publisher') }}
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Name') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Slug') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Website') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Publications') }}
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse($publishers as $publisher)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-building-office class="h-4 w-4 text-teal-500" />
                                <div>
                                    <div class="font-medium">{{ $publisher->name_en }}</div>
                                    @if($publisher->name_ru)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $publisher->name_ru }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">
                            {{ $publisher->slug }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            @if($publisher->website)
                                <a href="{{ $publisher->website }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-blue-600 transition-colors hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    <x-heroicon-o-globe-alt class="h-4 w-4" />
                                    {{ Str::limit($publisher->website, 25) }}
                                    <x-heroicon-o-arrow-top-right-on-square class="h-3 w-3" />
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                                {{ $publisher->publications_count ?? 0 }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editPublisher({{ $publisher->id }})"
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-blue-900/50 dark:hover:text-blue-400"
                                        title="{{ __('Edit') }}">
                                    <x-heroicon-o-pencil-square class="h-5 w-5" />
                                </button>
                                <button wire:click="confirmDelete('publisher', {{ $publisher->id }})"
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/50 dark:hover:text-red-400"
                                        title="{{ __('Delete') }}">
                                    <x-heroicon-o-trash class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-building-office class="h-12 w-12 text-gray-300 dark:text-gray-600" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No publishers found.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
