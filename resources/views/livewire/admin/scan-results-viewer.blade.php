<div>
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (!$scanJobId)
        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
            <p class="text-lg">{{ __('Start a bulk scan to view results here.') }}</p>
        </div>
    @elseif ($scanJob)
        <div class="bg-white dark:bg-gray-800 shadow-md rounded px-6 py-4 mb-4">
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Files') }}</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $scanJob->total_files_found }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Registered') }}</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $scanJob->files_registered }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Skipped') }}</div>
                    <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $scanJob->files_skipped }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Failed') }}</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ $scanJob->files_failed }}</div>
                </div>
            </div>
        </div>

        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex gap-2 flex-wrap">
                <button
                    wire:click="setFilter(null)"
                    class="px-4 py-2 rounded font-medium transition {{ $filterStatus === null ? 'bg-blue-600 dark:bg-blue-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                >
                    {{ __('All') }}
                </button>
                <button
                    wire:click="setFilter('processed')"
                    class="px-4 py-2 rounded font-medium transition {{ $filterStatus === 'processed' ? 'bg-green-600 dark:bg-green-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                >
                    {{ __('Success') }}
                </button>
                <button
                    wire:click="setFilter('failed')"
                    class="px-4 py-2 rounded font-medium transition {{ $filterStatus === 'failed' ? 'bg-red-600 dark:bg-red-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                >
                    {{ __('Failed') }}
                </button>
            </div>

            @if ($scanJob && $scanJob->files_registered > 0)
                <button
                    wire:click="bulkApprove"
                    wire:confirm="{{ __('Are you sure you want to approve all pending publications?') }}"
                    class="bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 text-white font-bold py-2 px-4 rounded transition"
                >
                    {{ __('Bulk Approve') }}
                </button>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">{{ __('File Path') }}</th>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">{{ __('Error Message') }}</th>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">{{ __('Registered At') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($results as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $log->file_path }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($log->status === 'processed')
                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">{{ __('Success') }}</span>
                                @elseif ($log->status === 'failed')
                                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded">{{ __('Failed') }}</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-red-600 dark:text-red-400">
                                {{ $log->error_message }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                {{ __('No results found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-gray-900 dark:text-gray-100">
            {{ $results->links() }}
        </div>
    @endif
</div>
