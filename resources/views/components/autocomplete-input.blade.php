@props([
    'wireModel',
    'searchMethod',
    'placeholder' => 'Search...',
    'createNewLabel' => 'Create new',
    'readonly' => false,
    'value' => null,
    'createNewModel' => null,
])

@php
    // Get the current value from Livewire using dot notation
    $currentValue = $value ?? data_get($this, $wireModel) ?? '';
    // Get create new state if model provided
    $shouldCreate = $createNewModel ? (data_get($this, $createNewModel) ?? false) : false;
@endphp

<div
    x-data="{
        open: false,
        search: @js($currentValue),
        selected: null,
        results: [],
        currentIndex: -1,
        exists: false,
        checking: false,
        shouldCreate: @js($shouldCreate),
        hasCreateModel: @js($createNewModel !== null),
        async fetchResults() {
            if (this.search.length < 2) {
                this.results = [];
                this.exists = false;
                return;
            }
            this.checking = true;
            try {
                this.results = await $wire.call('{{ $searchMethod }}', this.search);
                this.open = this.results.length > 0 || this.search.length >= 2;
                this.currentIndex = -1;
                // Check if exact match exists
                this.exists = this.results.some(r => r.name.toLowerCase() === this.search.toLowerCase());
            } catch (error) {
                console.error('Autocomplete search failed:', error);
                this.results = [];
                this.exists = false;
            } finally {
                this.checking = false;
            }
        },
        selectItem(item) {
            $wire.set('{{ $wireModel }}', item.name);
            this.search = item.name;
            this.exists = true;
            this.shouldCreate = false;
            if (this.hasCreateModel) {
                $wire.set('{{ $createNewModel ?? '' }}', false);
            }
            this.open = false;
            this.results = [];
        },
        syncToWire() {
            $wire.set('{{ $wireModel }}', this.search);
            // Re-check existence on blur
            if (this.search.length >= 2) {
                this.fetchResults().then(() => {
                    this.open = false;
                });
            }
        },
        toggleCreate() {
            this.shouldCreate = !this.shouldCreate;
            if (this.hasCreateModel) {
                $wire.set('{{ $createNewModel ?? '' }}', this.shouldCreate);
            }
        },
        async createNew() {
            const methodName = '{{ $searchMethod }}'.replace('search', 'createNew');
            try {
                const newItem = await $wire.call(methodName, this.search);
                this.selectItem(newItem);
                this.search = '';
            } catch (error) {
                console.error('Failed to create new item:', error);
            }
        }
    }"
    x-init="
        if (search.length >= 2) {
            fetchResults().then(() => { open = false; });
        }
    "
    @click.away="open = false"
    class="relative"
>
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <input
                type="text"
                x-model="search"
                @input.debounce.300ms="fetchResults()"
                @blur="syncToWire()"
                @keydown.arrow-down.prevent="
                    if (open && results.length > 0) {
                        currentIndex = Math.min(currentIndex + 1, results.length - 1);
                        $el.parentElement.querySelector('[x-ref=result-' + currentIndex + ']')?.scrollIntoView();
                    }
                "
                @keydown.arrow-up.prevent="
                    if (open && results.length > 0) {
                        currentIndex = Math.max(currentIndex - 1, -1);
                    }
                "
                @keydown.enter.prevent="
                    if (open && currentIndex >= 0 && results[currentIndex]) {
                        selectItem(results[currentIndex]);
                    }
                "
                @keydown.escape="open = false"
                @focus="if (search.length >= 2) { open = true; } else { fetchResults(); }"
                placeholder="{{ $placeholder }}"
                {{ $readonly ? 'readonly' : '' }}
                class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
            />

            <!-- Status Indicator -->
            <div class="absolute right-3 top-2.5 flex items-center gap-1">
                <!-- Loading Spinner -->
                <template x-if="checking">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>

                <!-- Exists Checkmark -->
                <template x-if="!checking && search.length >= 2 && exists">
                    <span class="text-green-500" title="Exists in database">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </template>

                <!-- Not Exists - Question Mark -->
                <template x-if="!checking && search.length >= 2 && !exists && !open">
                    <span class="text-yellow-500" title="Not found - check box to create">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </template>
            </div>
        </div>

        <!-- Create New Checkbox (shown when value doesn't exist) -->
        @if ($createNewModel)
            <template x-if="search.length >= 2 && !exists && !checking">
                <label class="flex items-center gap-1.5 cursor-pointer whitespace-nowrap text-sm" title="Create new entry on save">
                    <input
                        type="checkbox"
                        x-model="shouldCreate"
                        @change="toggleCreate()"
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                    />
                    <span class="text-yellow-600 dark:text-yellow-400">Create</span>
                </label>
            </template>
        @endif
    </div>

    <!-- Autocomplete Dropdown -->
    <div
        x-show="open && (results.length > 0 || (search.length >= 2 && @auth(){{ auth()->user()->role === 'admin' ? 'true' : 'false' }}@endauth))"
        x-transition
        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
    >
        <ul class="max-h-48 overflow-y-auto">
            <!-- Search Results -->
            <template x-for="(result, index) in results" :key="result.id">
                <li
                    :ref="'result-' + index"
                    @click="selectItem(result)"
                    :class="{
                        'bg-blue-50 dark:bg-blue-900/30': index === currentIndex,
                        'hover:bg-gray-50 dark:hover:bg-gray-700': index !== currentIndex
                    }"
                    class="px-3 py-2 cursor-pointer text-gray-900 dark:text-white text-sm flex items-center gap-2"
                    @mouseenter="currentIndex = index"
                    @mouseleave="currentIndex = -1"
                >
                    <span class="text-green-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span x-text="result.name"></span>
                </li>
            </template>

            <!-- Create New Option (Admin Only - instant create) -->
            @auth
                @if (auth()->user()->role === 'admin')
                    <template x-if="search.length >= 2 && results.length === 0">
                        <li
                            @click="createNew()"
                            class="px-3 py-2 cursor-pointer text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-sm font-medium border-t border-gray-200 dark:border-gray-700 flex items-center gap-2"
                        >
                            <span class="text-blue-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            {{ $createNewLabel }}: <span x-text="search"></span>
                        </li>
                    </template>
                @endif
            @endauth

            <!-- No Results (for non-admins) -->
            <template x-if="open && results.length === 0 && search.length >= 2 && @auth(){{ auth()->user()->role !== 'admin' ? 'true' : 'false' }}@endauth">
                <li class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm text-center">
                    No results found
                </li>
            </template>
        </ul>
    </div>
</div>
