@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => null, 'autoSubmit' => true, 'colspan' => null])

@php
    $colspanClass = $colspan ? "sm:col-span-{$colspan}" : '';
@endphp

<div {{ $attributes->merge(['class' => $colspanClass]) }}>
    <label for="{{ $name }}"
        class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
        {{ $label }}
    </label>
    <select name="{{ $name }}" id="{{ $name }}"
        class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px] px-3 cursor-pointer"
        @if($autoSubmit) onchange="this.form.submit()" @endif>

        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ ($selected ?? null) !== null && $value == $selected ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach

        {{ $slot }}
    </select>
</div>