@props(['field', 'value'])

@php
    $displayValue = null;

    switch($field->field_type) {
        case 'text':
        case 'long_text':
            $displayValue = is_array($value) ? ($value[0] ?? '') : $value;
            break;

        case 'number':
            $displayValue = is_array($value) ? ($value[0] ?? null) : $value;
            if ($displayValue !== null) {
                $displayValue = number_format((float)$displayValue, 2);
            }
            break;

        case 'date':
            $displayValue = is_array($value) ? ($value[0] ?? null) : $value;
            if ($displayValue) {
                try {
                    $displayValue = \Carbon\Carbon::parse($displayValue)->translatedFormat('d F Y');
                } catch (\Exception $e) {
                    $displayValue = $value;
                }
            }
            break;

        case 'dropdown':
            $rawValue = is_array($value) ? ($value[0] ?? null) : $value;
            if ($rawValue && isset($field->field_config['options'])) {
                $option = collect($field->field_config['options'])->firstWhere('value', $rawValue);
                if ($option) {
                    $locale = app()->getLocale();
                    $displayValue = $option["label_{$locale}"] ?? $option['label_en'] ?? $rawValue;
                } else {
                    $displayValue = $rawValue;
                }
            }
            break;

        case 'multiselect':
            $values = is_array($value) ? $value : [$value];
            if (!empty($values) && isset($field->field_config['options'])) {
                $labels = [];
                foreach ($values as $val) {
                    $option = collect($field->field_config['options'])->firstWhere('value', $val);
                    if ($option) {
                        $locale = app()->getLocale();
                        $labels[] = $option["label_{$locale}"] ?? $option['label_en'] ?? $val;
                    } else {
                        $labels[] = $val;
                    }
                }
                $displayValue = $labels;
            }
            break;

        case 'boolean':
            $rawValue = is_array($value) ? ($value[0] ?? false) : $value;
            $displayValue = $rawValue ? __('Yes') : __('No');
            break;

        default:
            $displayValue = is_array($value) ? ($value[0] ?? '') : $value;
    }
@endphp

@if($displayValue !== null && $displayValue !== '')
<div class="mb-3">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
        {{ $field->localized_label }}
    </dt>
    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
        @if($field->field_type === 'boolean')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displayValue === __('Yes') ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                {{ $displayValue }}
            </span>
        @elseif($field->field_type === 'multiselect' && is_array($displayValue))
            <div class="flex flex-wrap gap-1">
                @foreach($displayValue as $label)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        @elseif($field->field_type === 'long_text')
            <p class="whitespace-pre-wrap">{{ $displayValue }}</p>
        @else
            {{ $displayValue }}
        @endif
    </dd>
</div>
@endif
