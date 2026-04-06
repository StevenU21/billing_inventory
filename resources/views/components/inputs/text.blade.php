@props([
    'name' => null,
    'label' => null,
    'value' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'inputClass' => '',
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

    <div class="relative">
        <input
            type="{{ $type }}"
            @if($name)
                name="{{ $name }}"
                id="{{ $name }}"
            @endif
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class') }}
            class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px] px-3 placeholder-gray-400 dark:placeholder-gray-500 {{ isset($append) && trim((string) $append) !== '' ? 'pr-20' : '' }} {{ $readonly || $disabled ? 'opacity-60 cursor-not-allowed' : '' }} {{ $inputClass }}"
        />

        @if (isset($append) && trim((string) $append) !== '')
            <div class="absolute right-2 top-1/2 -translate-y-1/2">
                {{ $append }}
            </div>
        @endif
    </div>
</div>
