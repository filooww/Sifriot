<div class="p-6">
    {{-- Create Button --}}
    <div class="mb-6">
        <button wire:click="createContentType"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <x-heroicon-o-plus class="h-5 w-5" />
            {{ __('Create Content Type') }}
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Icon') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Name') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Slug') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('Folder') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('System') }}
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
                @forelse($contentTypes as $contentType)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($contentType->icon)
                                @php
                                    $icon = $contentType->icon;
                                    $isEmoji = preg_match('/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $icon);
                                @endphp
                                @if($isEmoji)
                                    <span class="text-3xl">{{ $icon }}</span>
                                @else
                                    <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-7 w-7 text-blue-600 dark:text-blue-400" />
                                @endif
                            @else
                                <x-heroicon-o-document class="h-7 w-7 text-gray-400 dark:text-gray-500" />
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <div class="font-medium">{{ $contentType->name_en }}</div>
                            @if($contentType->name_ru)
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $contentType->name_ru }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">
                            {{ $contentType->slug }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">
                            {{ $contentType->folder_name ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($contentType->is_system)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                    <x-heroicon-s-lock-closed class="h-3.5 w-3.5" />
                                    {{ __('System') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                                {{ $contentType->publications_count ?? 0 }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="editContentType({{ $contentType->id }})"
                                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-blue-900/50 dark:hover:text-blue-400"
                                        title="{{ __('Edit') }}">
                                    <x-heroicon-o-pencil-square class="h-5 w-5" />
                                </button>
                                @if(!$contentType->is_system)
                                    <button wire:click="deleteContentType({{ $contentType->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this content type?') }}"
                                            class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/50 dark:hover:text-red-400"
                                            title="{{ __('Delete') }}">
                                        <x-heroicon-o-trash class="h-5 w-5" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-folder-open class="h-12 w-12 text-gray-300 dark:text-gray-600" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No content types found.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
