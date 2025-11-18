@props([
    'wireModel',
    'searchMethod',
    'placeholder' => 'Search...',
    'createNewLabel' => 'Create new',
    'readonly' => false,
])

<div
    x-data="{
        open: false,
        search: '',
        selected: null,
        results: [],
        currentIndex: -1,
        async fetchResults() {
            if (this.search.length < 2) {
                this.results = [];
                return;
            }
            try {
                this.results = await $wire.call('{{ $searchMethod }}', this.search);
                this.open = this.results.length > 0 || this.search.length >= 2;
                this.currentIndex = -1;
            } catch (error) {
                console.error('Autocomplete search failed:', error);
                this.results = [];
            }
        },
        selectItem(item) {
            $wire.set('{{ $wireModel }}', item.name);
            this.search = item.name;
            this.open = false;
            this.results = [];
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
    @click.away="open = false"
    class="relative"
>
    <input
        type="text"
        x-model="search"
        @input.debounce.300ms="fetchResults()"
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
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
    />

    <!-- Loading Spinner -->
    <div x-show="search.length >= 2 && results.length === 0 && open" class="absolute right-3 top-2.5">
        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
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
                    class="px-3 py-2 cursor-pointer text-gray-900 dark:text-white text-sm"
                    @mouseenter="currentIndex = index"
                    @mouseleave="currentIndex = -1"
                >
                    <span x-text="result.name"></span>
                </li>
            </template>

            <!-- Create New Option (Admin Only) -->
            @auth
                @if (auth()->user()->role === 'admin')
                    <template x-if="search.length >= 2 && results.length === 0">
                        <li
                            @click="createNew()"
                            class="px-3 py-2 cursor-pointer text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-sm font-medium border-t border-gray-200 dark:border-gray-700"
                        >
                            ✚ {{ $createNewLabel }}: <span x-text="search"></span>
                        </li>
                    </template>
                @endif
            @endauth

            <!-- No Results -->
            <template x-if="open && results.length === 0 && search.length >= 2 && @auth(){{ auth()->user()->role !== 'admin' ? 'true' : 'false' }}@endauth">
                <li class="px-3 py-2 text-gray-500 dark:text-gray-400 text-sm text-center">
                    No results found
                </li>
            </template>
        </ul>
    </div>
</div>
