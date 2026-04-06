@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger
    'icon' => null,
    'iconOnly' => false,
])

@php
    $variantClasses = [
        'primary' => 'bg-purple-600 hover:bg-purple-700 text-white shadow-lg shadow-purple-900/50',
        'secondary' => 'bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-900/50',
    ];

    $classes = $variantClasses[$variant] ?? $variantClasses['primary'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg transition-colors font-medium text-sm {$classes}"]) }}
>
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif

    @if(!$iconOnly)
        {{ $slot }}
    @endif
</button>
