<div class="space-y-6">
    <!-- Validation Errors Alert -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-red-800 dark:text-red-200 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-700 dark:text-red-300">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- File Info Header -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $fileMetadata->file_name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Format: <span class="font-medium">{{ strtoupper(pathinfo($fileMetadata->file_name, PATHINFO_EXTENSION)) }}</span>
                </p>
            </div>
            <div class="text-right">
                @if ($extractionStatus === 'processed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        📋 Ready for Review
                    </span>
                @elseif ($extractionStatus === 'confirmed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        ✅ Confirmed
                    </span>
                @elseif ($extractionStatus === 'rejected')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                        🚫 Rejected - Edit & Confirm
                    </span>
                @elseif ($extractionStatus === 'failed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                        ❌ Failed
                    </span>
                @elseif ($extractionStatus === 'pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                        ⏳ Processing
                    </span>
                @endif
            </div>
        </div>

        @if ($extractionStatus === 'pending')
            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">Extraction in progress...</p>
            </div>
        @endif

        @if ($extractionStatus === 'failed' && $errorMessage)
            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>Error:</strong> {{ $errorMessage }}
                </p>
            </div>
        @endif
    </div>

    <!-- File Content Preview -->
    @php
        $publication = \App\Models\Publication::with('files')->find($fileMetadata->file_id);
    @endphp
    @if($publication)
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <button
                type="button"
                wire:click="$toggle('showFilePreview')"
                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
            >
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">📄 File Preview</span>
                <svg class="w-5 h-5 transition transform {{ $showFilePreview ?? false ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>

            @if($showFilePreview ?? false)
                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <livewire:publications.document-viewer
                        :publicationId="$publication->id_publication"
                        :key="'metadata-viewer-' . $fileMetadata->id"
                    />
                </div>
            @endif
        </div>
    @endif

    <!-- Extraction Details (Collapsible) -->
    @if ($useExtracted && count($confidenceScores) > 0)
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <button
                type="button"
                wire:click="toggleDetails"
                class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
            >
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Extraction Details</span>
                <svg class="w-5 h-5 transition transform {{ $showDetails ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>

            @if ($showDetails)
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Extractor</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $extractionMethod ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Extracted At</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $fileMetadata->extracted_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if ($extractionStatus === 'confirmed')
                        <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                <strong>Confirmed:</strong> {{ $fileMetadata->confirmed_at?->format('M d, Y H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- Metadata Form -->
    <form class="space-y-8">
        <!-- Form Section: Basic Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                📝 Basic Information
            </h3>
            <div class="space-y-4">
        <!-- Title Field -->
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Title
                @if ($useExtracted && isset($confidenceScores['title']))
                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['title']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['title']) }}-800">
                        {{ $this->getConfidencePercent('title') }}% confident
                    </span>
                @endif
            </label>
            <input
                type="text"
                id="title"
                wire:model="title"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                required
            />
            @error('title')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Authors Field -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Authors
                @if ($useExtracted && isset($confidenceScores['authors']))
                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['authors']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['authors']) }}-800">
                        {{ $this->getConfidencePercent('authors') }}% confident
                    </span>
                @endif
            </label>
            <div class="space-y-2">
                @foreach ($authors as $index => $author)
                    <div class="flex gap-2">
                        <x-autocomplete-input
                            wireModel="authors.{{ $index }}"
                            searchMethod="searchAuthors"
                            placeholder="Author name"
                            createNewLabel="Create new author"
                        />
                        @if (count($authors) > 1)
                            <button
                                type="button"
                                wire:click="removeAuthor({{ $index }})"
                                class="px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                            >
                                Remove
                            </button>
                        @endif
                    </div>
                @endforeach
                <button
                    type="button"
                    wire:click="addAuthor"
                    class="px-4 py-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition text-sm font-medium"
                >
                    + Add Author
                </button>
            </div>
            @error('authors')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

            </div>
        </div>

        <!-- Form Section: Publication Details -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                📚 Publication Details
            </h3>
            <div class="space-y-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Publication Year -->
                <div>
                    <label for="publicationYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Publication Year
                        @if ($useExtracted && isset($confidenceScores['publication_year']))
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['publication_year']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['publication_year']) }}-800">
                                {{ $this->getConfidencePercent('publication_year') }}% confident
                            </span>
                        @endif
                    </label>
                    <input
                        type="number"
                        id="publicationYear"
                        wire:model="publicationYear"
                        min="1000"
                        max="2100"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                    />
                    @error('publicationYear')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content Type -->
                <div>
                    <label for="contentTypeId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Content Type
                    </label>
                    <select
                        id="contentTypeId"
                        wire:model="contentTypeId"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white appearance-none cursor-pointer [color-scheme:light] dark:[color-scheme:dark]"
                    >
                        <option value="">-- Select Content Type --</option>
                        @foreach (\App\Models\ContentType::all() as $contentType)
                            <option value="{{ $contentType->id }}">{{ $contentType->name_en }}</option>
                        @endforeach
                    </select>
                    @error('contentTypeId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-4 mt-4">
                <!-- Publisher Field -->
                <div>
                    <label for="publisher" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Publisher
                        @if ($useExtracted && isset($confidenceScores['publisher']))
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['publisher']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['publisher']) }}-800">
                                {{ $this->getConfidencePercent('publisher') }}% confident
                            </span>
                        @endif
                    </label>
            <x-autocomplete-input
                wireModel="publisher"
                searchMethod="searchPublishers"
                placeholder="Publisher name"
                createNewLabel="Create new publisher"
            />
                    @error('publisher')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ISBN Field -->
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        ISBN
                        @if ($useExtracted && isset($confidenceScores['isbn']))
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['isbn']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['isbn']) }}-800">
                                {{ $this->getConfidencePercent('isbn') }}% confident
                            </span>
                        @endif
                    </label>
                    <input
                        type="text"
                        id="isbn"
                        wire:model="isbn"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                    />
                    @error('isbn')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- DOI Field -->
                <div>
                    <label for="doi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        DOI
                        @if ($useExtracted && isset($confidenceScores['doi']))
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-{{ $this->getConfidenceColor($confidenceScores['doi']) }}-100 text-{{ $this->getConfidenceColor($confidenceScores['doi']) }}-800">
                                {{ $this->getConfidencePercent('doi') }}% confident
                            </span>
                        @endif
                    </label>
                    <input
                        type="text"
                        id="doi"
                        wire:model="doi"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                    />
                    @error('doi')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Section: Categorization -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                🏷️ Categorization
            </h3>
            <div class="space-y-4">
                <!-- Genres Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Genres
                    </label>
            <div class="space-y-2">
                @foreach ($genres as $index => $genre)
                    <div class="flex gap-2">
                        <x-autocomplete-input
                            wireModel="genres.{{ $index }}"
                            searchMethod="searchGenres"
                            placeholder="Genre"
                            createNewLabel="Create new genre"
                        />
                        @if (count($genres) > 1)
                            <button
                                type="button"
                                wire:click="removeGenre({{ $index }})"
                                class="px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                            >
                                Remove
                            </button>
                        @endif
                    </div>
                @endforeach
                <button
                    type="button"
                    wire:click="addGenre"
                    class="px-4 py-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition text-sm font-medium"
                >
                    + Add Genre
                </button>
                    </div>
                    @error('genres')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Theme Field -->
                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Theme/Category
                    </label>
                    <x-autocomplete-input
                        wireModel="theme"
                        searchMethod="searchThemes"
                        placeholder="Theme or category"
                        createNewLabel="Create new theme"
                    />
                    @error('theme')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Section: Media & Additional Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                🖼️ Media & Additional Info
            </h3>
            <div class="space-y-4">

        <!-- Description Field -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
            </label>
            <textarea
                id="description"
                wire:model="description"
                rows="4"
                placeholder="Publication description or summary..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
            ></textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 1000 characters</p>
            @error('description')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Cover Image Field -->
        <div>
            <label for="coverImage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Cover Image
            </label>
            <div class="space-y-3">
                <input
                    type="file"
                    id="coverImage"
                    wire:model="coverImage"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/30"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG or WebP. Max 5MB.</p>

                @php
                    $currentCoverUrl = $this->getCurrentCoverImageUrl();
                @endphp

                @if ($currentCoverUrl && !$coverImage)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Cover Image:</p>
                        <div class="w-32 h-48 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                            <img src="{{ $currentCoverUrl }}" alt="Current cover" class="w-full h-full object-cover">
                        </div>
                    </div>
                @elseif ($coverImage)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Cover Preview:</p>
                        <div class="w-32 h-48 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                            <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover preview" class="w-full h-full object-cover">
                        </div>
                    </div>
                @endif
            </div>
                @error('coverImage')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-50 dark:from-gray-800 dark:to-gray-800 border-t border-gray-200 dark:border-gray-700 -mx-8 -mb-8 px-8 py-6 mt-8">
            <div class="flex flex-wrap gap-3 justify-end">
                @if ($useExtracted && $extractionStatus === 'processed')
                    <!-- For processed: confirm or reject -->
                    <button
                        type="button"
                        wire:click="rejectExtraction"
                        class="px-6 py-2.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-semibold rounded-lg transition flex items-center gap-2"
                    >
                        <span>❌</span> Reject & Edit
                    </button>
                    <button
                        type="button"
                        wire:click="confirmExtraction"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition flex items-center gap-2 shadow-md"
                    >
                        <span>✅</span> Confirm Extraction
                    </button>
                @elseif ($useExtracted && $extractionStatus === 'rejected')
                    <!-- For rejected: save changes or confirm as pending -->
                    <button
                        type="button"
                        wire:click="updateMetadata"
                        class="px-6 py-2.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-semibold rounded-lg transition flex items-center gap-2"
                    >
                        <span>💾</span> Save Changes
                    </button>
                    <button
                        type="button"
                        wire:click="confirmExtraction"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition flex items-center gap-2 shadow-md"
                    >
                        <span>✅</span> Confirm & Mark Pending
                    </button>
                @elseif ($extractionStatus === 'confirmed')
                    <!-- For confirmed: save updates -->
                    <button
                        type="button"
                        wire:click="updateMetadata"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition flex items-center gap-2 shadow-md"
                    >
                        <span>💾</span> Save Changes
                    </button>
                @elseif ($useManual || $extractionStatus === 'failed')
                    <!-- For manual entry or failed: save and confirm -->
                    <button
                        type="button"
                        wire:click="saveManualEntry"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition flex items-center gap-2 shadow-md"
                    >
                        <span>💾</span> Save & Confirm
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
