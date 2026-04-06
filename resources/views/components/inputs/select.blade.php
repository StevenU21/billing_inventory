@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Seleccionar...',
    'required' => false,
])

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select
        @if($name)
            name="{{ $name }}"
            id="{{ $name }}"
        @endif
        {{ $required ? 'required' : '' }}
        {{ $attributes->except('class') }}
        class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px] px-3 cursor-pointer"
    >
        @if($placeholder && empty($options['']))
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ (string)$selected === (string)$value ? 'selected' : '' }}>
                {{ is_string($label) || is_numeric($label) ? $label : json_encode($label) }}
            </option>
        @endforeach
    </select>
</div>
