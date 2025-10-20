<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8">{{ __('File Registration') }}</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Mode Selection -->
    <div class="mb-6">
        <label class="block text-sm font-medium mb-2">{{ __('Registration Mode') }}</label>
        <div class="flex gap-4">
            <label class="flex items-center">
                <input type="radio" wire:model.live="registrationMode" value="register_existing" class="me-2">
                {{ __('Register Existing File') }}
            </label>
            <label class="flex items-center">
                <input type="radio" wire:model.live="registrationMode" value="upload_new" class="me-2">
                {{ __('Upload New File') }}
            </label>
        </div>
    </div>

    <!-- Registration Form -->
    <form wire:submit.prevent="{{ $registrationMode === 'upload_new' ? 'uploadFile' : 'registerFile' }}" class="space-y-6">

        @if($registrationMode === 'upload_new')
            <!-- Upload File Section -->
            <div>
                <label class="block text-sm font-medium mb-2">{{ __('Select File') }}</label>
                <input type="file" wire:model="uploadedFile" class="block w-full border border-gray-300 rounded px-3 py-2">
                @error('uploadedFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Max file size: 500MB') }}<br>
                    {{ __('Allowed formats: PDF, EPUB, TXT, DOCX') }}
                </p>

                <!-- Upload Progress Indicator -->
                <div wire:loading wire:target="uploadedFile" class="mt-2">
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                        {{ __('Uploading...') }}
                    </div>
                </div>
            </div>
        @else
            <!-- Selected File Path (for existing file registration) -->
            @if($selectedFilePath)
                <div class="bg-gray-100 p-4 rounded">
                    <p class="text-sm"><strong>{{ __('Selected File') }}:</strong> {{ basename($selectedFilePath) }}</p>
                    <p class="text-sm text-gray-600">{{ $selectedFilePath }}</p>
                </div>
            @endif
        @endif

        <!-- Publication Title -->
        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Title') }}</label>
            <input type="text" wire:model="publicationTitle" class="block w-full border border-gray-300 rounded px-3 py-2">
            @error('publicationTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Content Type Selection -->
        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Content Type') }}</label>
            <select wire:model="contentTypeId" class="block w-full border border-gray-300 rounded px-3 py-2">
                <option value="">{{ __('Select Content Type') }}</option>
                @foreach($contentTypes as $type)
                    <option value="{{ $type->id }}">
                        {{ app()->getLocale() === 'ru' ? $type->name_ru : (app()->getLocale() === 'he' ? $type->name_he : $type->name_en) }}
                    </option>
                @endforeach
            </select>
            @error('contentTypeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-4">
            @if($registrationMode === 'upload_new')
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    {{ __('Upload File') }}
                </button>
            @else
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    {{ __('Register Selected') }}
                </button>
            @endif

            <a href="{{ route('admin.files.browse') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                {{ __('Cancel') }}
            </a>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="uploadFile,registerFile" class="mt-4">
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                {{ __('Processing...') }}
            </div>
        </div>
    </form>
</div>
