@props(['field', 'value' => null, 'wireModel'])

<div class="mb-4">
    <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $field->localized_label }}
        @if($field->is_required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @switch($field->field_type)
        @case('text')
            <input type="text"
                   id="{{ $field->field_name }}"
                   wire:model="{{ $wireModel }}"
                   {{ $field->is_required ? 'required' : '' }}
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @break

        @case('number')
            <input type="number"
                   id="{{ $field->field_name }}"
                   wire:model="{{ $wireModel }}"
                   {{ $field->is_required ? 'required' : '' }}
                   @if(isset($field->field_config['min'])) min="{{ $field->field_config['min'] }}" @endif
                   @if(isset($field->field_config['max'])) max="{{ $field->field_config['max'] }}" @endif
                   @if(isset($field->field_config['step'])) step="{{ $field->field_config['step'] }}" @endif
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @break

        @case('date')
            <input type="date"
                   id="{{ $field->field_name }}"
                   wire:model="{{ $wireModel }}"
                   {{ $field->is_required ? 'required' : '' }}
                   @if(isset($field->field_config['min_date'])) min="{{ $field->field_config['min_date'] }}" @endif
                   @if(isset($field->field_config['max_date'])) max="{{ $field->field_config['max_date'] }}" @endif
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @break

        @case('dropdown')
            <select id="{{ $field->field_name }}"
                    wire:model="{{ $wireModel }}"
                    {{ $field->is_required ? 'required' : '' }}
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">{{ __('Select an option') }}</option>
                @if(isset($field->field_config['options']))
                    @foreach($field->field_config['options'] as $option)
                        <option value="{{ $option['value'] }}">
                            {{ $option['label_' . app()->getLocale()] ?? $option['label_en'] ?? $option['value'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break

        @case('multiselect')
            <select id="{{ $field->field_name }}"
                    wire:model="{{ $wireModel }}"
                    multiple
                    {{ $field->is_required ? 'required' : '' }}
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @if(isset($field->field_config['options']))
                    @foreach($field->field_config['options'] as $option)
                        <option value="{{ $option['value'] }}">
                            {{ $option['label_' . app()->getLocale()] ?? $option['label_en'] ?? $option['value'] }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break

        @case('boolean')
            <div class="mt-1 flex items-center">
                <input type="checkbox"
                       id="{{ $field->field_name }}"
                       wire:model="{{ $wireModel }}"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <label for="{{ $field->field_name }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    {{ __('Yes') }}
                </label>
            </div>
            @break

        @case('long_text')
            <textarea id="{{ $field->field_name }}"
                      wire:model="{{ $wireModel }}"
                      {{ $field->is_required ? 'required' : '' }}
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            @break
    @endswitch

    @error($wireModel)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
