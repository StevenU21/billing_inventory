@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'rows' => 3,
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

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 placeholder-gray-400 dark:placeholder-gray-500 {{ $readonly || $disabled ? 'opacity-60 cursor-not-allowed' : '' }}"
    >{{ $value }}</textarea>
</div>
