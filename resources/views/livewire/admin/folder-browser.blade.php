<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8">{{ __('Browse Server Files') }}</h1>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Breadcrumb Navigation -->
    <nav class="mb-6 text-sm">
        @foreach($breadcrumbs as $index => $crumb)
            @if($loop->last)
                <span class="text-gray-700 font-semibold">{{ $crumb['name'] }}</span>
            @else
                <button wire:click="loadFolder('{{ $crumb['path'] }}')" class="text-blue-600 hover:underline">
                    {{ $crumb['name'] }}
                </button>
                <span class="text-gray-400 mx-2">/</span>
            @endif
        @endforeach
    </nav>

    <!-- Action Buttons -->
    <div class="mb-6 flex gap-4">
        <button wire:click="registerSelected" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            {{ __('Register Selected') }}
        </button>
        <a href="{{ route('admin.files.register') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            {{ __('Upload New File') }}
        </a>
    </div>

    <!-- Folders List -->
    @if(count($folders) > 0)
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">{{ __('Folders') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($folders as $folder)
                    <button wire:click="loadFolder('{{ $folder['path'] }}')"
                            class="flex items-center gap-2 p-4 border rounded hover:bg-gray-100 text-start">
                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                        </svg>
                        <span class="font-medium">{{ $folder['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Files List -->
    @if(count($files) > 0)
        <div>
            <h2 class="text-xl font-semibold mb-3">{{ __('Files') }} ({{ count($files) }})</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-start">{{ __('Select') }}</th>
                            <th class="px-4 py-2 border-b text-start">{{ __('File Name') }}</th>
                            <th class="px-4 py-2 border-b text-start">{{ __('File Size') }}</th>
                            <th class="px-4 py-2 border-b text-start">{{ __('Modified Date') }}</th>
                            <th class="px-4 py-2 border-b text-start">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                            <tr class="hover:bg-gray-50 {{ $file['is_registered'] ? 'text-gray-400' : '' }}">
                                <td class="px-4 py-2 border-b">
                                    @if(!$file['is_registered'])
                                        <input type="checkbox" wire:model="selectedFiles" value="{{ $file['path'] }}" class="rounded">
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="{{ !$file['is_registered'] ? 'font-semibold' : '' }}">
                                            {{ $file['name'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ number_format($file['size'] / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ date('Y-m-d H:i', $file['modified_date']) }}
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if($file['is_registered'])
                                        <span class="text-green-600 text-sm">{{ __('Registered') }}</span>
                                    @else
                                        <span class="text-orange-600 text-sm font-semibold">{{ __('Unregistered') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Load More Button (for pagination) -->
            @if(count($files) >= $currentDisplayCount)
                <div class="mt-4 text-center">
                    <button wire:click="loadMoreFiles" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        {{ __('Load More') }}
                    </button>
                </div>
            @endif
        </div>
    @else
        <div class="text-center text-gray-500 py-8">
            {{ __('No files found in this folder.') }}
        </div>
    @endif
</div>
