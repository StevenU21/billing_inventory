@props([
    'label' => null,
    'value' => null,
    'icon' => null,
    'font' => 'sans', // sans, mono
])
@php
    $fontClasses = [
        'sans' => 'font-sans',
        'mono' => 'font-mono',
    ][$font];
@endphp

<div {{ $attributes->merge(['class' => 'flex justify-between items-center py-2 px-3 rounded-lg bg-white dark:bg-gray-800']) }}>
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
        @if($icon)
            <i class="fas {{ $icon }} mr-1.5"></i>
        @endif
        {{ $label }}
    </span>
    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 {{ $fontClasses }}">
        {{ $value ?? $slot }}
    </span>
</div>
