@props([
    'type' => 'button',
    'variant' => 'secondary',
])

@php
    $baseClasses = 'px-4 py-2 rounded-lg border transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-purple-600 hover:bg-purple-700 text-white border-transparent focus:ring-purple-500 shadow-lg shadow-purple-900/50',
        'secondary' => 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 border-gray-600 text-gray-700 dark:text-gray-200 focus:ring-gray-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white border-transparent focus:ring-red-500 shadow-lg shadow-red-900/50',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['secondary']);
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
