{{-- Delete Confirmation Modal --}}
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="cancelDelete"></div>

        {{-- Center modal --}}
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

        <div class="inline-block transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
            <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    {{-- Icon --}}
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ count($deletePublications) > 0 || $deleteChildrenCount > 0 ? 'bg-amber-100 dark:bg-amber-900/50' : 'bg-red-100 dark:bg-red-900/50' }} sm:mx-0 sm:h-10 sm:w-10">
                        @if(count($deletePublications) > 0 || $deleteChildrenCount > 0)
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        @else
                            <x-heroicon-o-trash class="h-6 w-6 text-red-600 dark:text-red-400" />
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                            Удалить «{{ $deleteName }}»
                        </h3>

                        @if($deleteChildrenCount > 0)
                            {{-- Cannot delete: has child sections --}}
                            <div class="mt-4 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800">
                                <div class="flex items-center gap-2 text-red-700 dark:text-red-400">
                                    <x-heroicon-o-x-circle class="h-5 w-5 flex-shrink-0" />
                                    <span class="font-medium">Невозможно удалить раздел с дочерними разделами</span>
                                </div>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-300">
                                    Этот раздел содержит {{ $deleteChildrenCount }} дочерних раздел(а/ов). Сначала удалите или переместите их.
                                </p>
                            </div>

                        @elseif(count($deletePublications) > 0)
                            {{-- Has publications: show list and options --}}
                            <div class="mt-4 space-y-4">
                                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/30 p-3 border border-amber-200 dark:border-amber-800">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-2">
                                        Этот элемент используется в {{ count($deletePublications) }} публикации(ях):
                                    </p>

                                    {{-- Publications list with links and per-publication replacement --}}
                                    <div class="max-h-64 overflow-y-auto space-y-2">
                                        @foreach($deletePublications as $pub)
                                            <div class="flex items-center gap-2 bg-white/50 dark:bg-gray-800/50 rounded-lg p-2">
                                                <x-heroicon-o-document-text class="h-4 w-4 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                                                <a href="{{ route('publications.show', $pub['id']) }}" 
                                                   target="_blank"
                                                   class="flex-1 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline truncate">
                                                    {{ $pub['title'] }}
                                                    <x-heroicon-o-arrow-top-right-on-square class="inline h-2.5 w-2.5 ml-0.5 opacity-60" />
                                                </a>

                                                @if(!$applyToAll && count($replacementOptions) > 0)
                                                    <select wire:model.live="publicationReplacements.{{ $pub['id'] }}"
                                                            class="ml-auto text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 w-40">
                                                        <option value="">-- Выбрать --</option>
                                                        @foreach($replacementOptions as $option)
                                                            <option value="{{ $option['id'] }}">{{ Str::limit($option['name'], 20) }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if(count($replacementOptions) > 0)
                                    {{-- Toggle between apply-to-all and per-publication --}}
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model.live="applyToAll" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            @if($applyToAll)
                                                Заменить во всех публикациях одним значением
                                            @else
                                                Выбрать замену для каждой публикации отдельно
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Global replace dropdown (shown when applyToAll is true) --}}
                                    @if($applyToAll)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Заменить на:
                                            </label>
                                            <select wire:model.live="replaceWithId"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">-- Выберите замену --</option>
                                                @foreach($replacementOptions as $option)
                                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
                            </div>

                        @else
                            {{-- No publications: simple confirm --}}
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Вы уверены, что хотите удалить этот элемент? Это действие нельзя отменить.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                @if($deleteChildrenCount > 0)
                    {{-- Only cancel for child sections --}}
                    <button wire:click="cancelDelete"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto">
                        Закрыть
                    </button>

                @elseif(count($deletePublications) > 0)
                    {{-- Has publications: show replace and detach options --}}
                    <button wire:click="detachAndDelete"
                            type="button"
                            class="inline-flex justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 sm:w-auto">
                        <x-heroicon-o-link-slash class="h-4 w-4 mr-1.5 -ml-0.5" />
                        Отвязать и удалить
                    </button>

                    @if(count($replacementOptions) > 0)
                        @php
                            $canReplace = $applyToAll 
                                ? (bool)$replaceWithId 
                                : count(array_filter($publicationReplacements, fn($v) => $v)) === count($deletePublications);
                        @endphp
                        <button wire:click="replaceAndDelete"
                                type="button"
                                @if(!$canReplace) disabled @endif
                                class="inline-flex justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                            <x-heroicon-o-arrow-path class="h-4 w-4 mr-1.5 -ml-0.5" />
                            Заменить и удалить
                        </button>
                    @endif

                    <button wire:click="cancelDelete"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto">
                        Отмена
                    </button>

                @else
                    {{-- No publications: simple delete --}}
                    <button wire:click="executeDelete"
                            type="button"
                            class="inline-flex justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 sm:w-auto">
                        Удалить
                    </button>

                    <button wire:click="cancelDelete"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto">
                        Отмена
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
