<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">{{ __('File Registration') }}</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Mode Selection -->
    <div class="mb-6">
        <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Registration Mode') }}</label>
        <div class="flex gap-4">
            <label class="flex items-center text-gray-900 dark:text-gray-100">
                <input type="radio" wire:model.live="registrationMode" value="register_existing" class="me-2 dark:bg-gray-700 dark:border-gray-600">
                {{ __('Register Existing File') }}
            </label>
            <label class="flex items-center text-gray-900 dark:text-gray-100">
                <input type="radio" wire:model.live="registrationMode" value="upload_new" class="me-2 dark:bg-gray-700 dark:border-gray-600">
                {{ __('Upload New File') }}
            </label>
        </div>
    </div>

    <!-- Registration Form -->
    <form wire:submit.prevent="{{ $registrationMode === 'upload_new' ? 'uploadFile' : 'registerFile' }}" class="space-y-6">

        @if($registrationMode === 'upload_new')
            <!-- Upload File Section -->
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Select File') }}</label>
                <input type="file" wire:model="uploadedFile" class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                @error('uploadedFile') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Max file size: 500MB') }}<br>
                    {{ __('Allowed formats: PDF, EPUB, TXT, DOCX') }}
                </p>

                <!-- Upload Progress Indicator -->
                <div wire:loading wire:target="uploadedFile" class="mt-2">
                    <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded">
                        {{ __('Uploading...') }}
                    </div>
                </div>
            </div>
        @else
            <!-- Selected File Path (for existing file registration) -->
            @if($selectedFilePath)
                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-700">
                    <p class="text-sm text-gray-900 dark:text-gray-100"><strong>{{ __('Selected File') }}:</strong> {{ basename($selectedFilePath) }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedFilePath }}</p>
                </div>
            @endif
        @endif

        <!-- Publication Title -->
        <div>
            <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Title') }}</label>
            <input type="text" wire:model="publicationTitle" class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            @error('publicationTitle') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Content Type Selection -->
        <div>
            <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">{{ __('Content Type') }}</label>
            <select wire:model.live="contentTypeId" class="block w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <option value="">{{ __('Select Content Type') }}</option>
                @foreach($contentTypes as $type)
                    <option value="{{ $type->id }}">
                        {{ app()->getLocale() === 'ru' ? $type->name_ru : (app()->getLocale() === 'he' ? $type->name_he : $type->name_en) }}
                    </option>
                @endforeach
            </select>
            @error('contentTypeId') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Custom Fields Section -->
        @if(!empty($customFields))
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ __('Custom Fields') }}</h3>

                @foreach($customFields as $field)
                    <div class="mb-4">
                        @php
                            $locale = app()->getLocale();
                            $labelKey = "label_{$locale}";
                            $label = $field[$labelKey] ?? $field['label_en'];
                        @endphp

                        <x-custom-field-input
                            :field="$field"
                            :value="$customFieldValues[$field['field_name']] ?? null"
                            wire:model="customFieldValues.{{ $field['field_name'] }}"
                        />

                        @error("customFieldValues.{$field['field_name']}")
                            <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Submit Buttons -->
        <div class="flex gap-4">
            @if($registrationMode === 'upload_new')
                <button type="submit" class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-700 dark:hover:bg-blue-800">
                    {{ __('Upload File') }}
                </button>
            @else
                <button type="submit" class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-700 dark:hover:bg-blue-800">
                    {{ __('Register Selected') }}
                </button>
            @endif

            <a href="{{ route('admin.files.browse') }}" class="bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-6 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600">
                {{ __('Cancel') }}
            </a>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="uploadFile,registerFile" class="mt-4">
            <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-200 px-4 py-3 rounded">
                {{ __('Processing...') }}
            </div>
        </div>
    </form>
</div>
