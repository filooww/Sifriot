<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4">{{ __('Scan Results') }}</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if ($scanJob)
            <div class="bg-white shadow-md rounded px-6 py-4 mb-4">
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-600">{{ __('Total Files') }}</div>
                        <div class="text-xl font-bold">{{ $scanJob->total_files_found }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">{{ __('Registered') }}</div>
                        <div class="text-xl font-bold text-green-600">{{ $scanJob->files_registered }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">{{ __('Skipped') }}</div>
                        <div class="text-xl font-bold text-yellow-600">{{ $scanJob->files_skipped }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">{{ __('Failed') }}</div>
                        <div class="text-xl font-bold text-red-600">{{ $scanJob->files_failed }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="mb-4 flex justify-between items-center">
        <div class="flex gap-2">
            <button
                wire:click="setFilter(null)"
                class="px-4 py-2 rounded {{ $filterStatus === null ? 'bg-blue-500 text-white' : 'bg-gray-200' }}"
            >
                {{ __('All') }}
            </button>
            <button
                wire:click="setFilter('processed')"
                class="px-4 py-2 rounded {{ $filterStatus === 'processed' ? 'bg-green-500 text-white' : 'bg-gray-200' }}"
            >
                {{ __('Success') }}
            </button>
            <button
                wire:click="setFilter('failed')"
                class="px-4 py-2 rounded {{ $filterStatus === 'failed' ? 'bg-red-500 text-white' : 'bg-gray-200' }}"
            >
                {{ __('Failed') }}
            </button>
        </div>

        @if ($scanJob && $scanJob->files_registered > 0)
            <button
                wire:click="bulkApprove"
                wire:confirm="{{ __('Are you sure you want to approve all pending publications?') }}"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
            >
                {{ __('Bulk Approve') }}
            </button>
        @endif
    </div>

    <div class="bg-white shadow-md rounded overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('File Path') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('Error Message') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">{{ __('Registered At') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($results as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <span class="font-mono text-xs">{{ $log->file_path }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($log->status === 'processed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded">{{ __('Success') }}</span>
                            @elseif ($log->status === 'failed')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded">{{ __('Failed') }}</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded">{{ ucfirst($log->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-red-600">
                            {{ $log->error_message }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No results found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $results->links() }}
    </div>
</div>
