@props([
    'variant' => 'default', // default, muted, highlight, error, success
    'align' => 'left', // left, center, right
    'size' => 'sm', // xs, sm, base, lg
    'font' => 'sans', // sans, mono
])
@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align];

    $variantClasses = [
        'default' => 'text-gray-700 dark:text-gray-300',
        'muted' => 'text-gray-500 dark:text-gray-400',
        'highlight' => 'text-gray-900 dark:text-gray-100 font-medium',

        // Semantic aliases
        'error' => 'text-red-600 dark:text-red-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'warning' => 'text-orange-500 dark:text-orange-400',
        'info' => 'text-blue-600 dark:text-blue-400',

        // Standard Colors (para compatibilidad con funciones color())
        'red' => 'text-red-600 dark:text-red-400',
        'green' => 'text-emerald-600 dark:text-emerald-400',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'purple' => 'text-purple-600 dark:text-purple-400',
        'yellow' => 'text-yellow-600 dark:text-yellow-400',
        'orange' => 'text-orange-600 dark:text-orange-400',
        'gray' => 'text-gray-600 dark:text-gray-400',
        'pink' => 'text-pink-600 dark:text-pink-400',
    ][$variant] ?? 'text-gray-700 dark:text-gray-300'; // Fallback a default si no existe

    $sizeClasses = [
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
    ][$size];

    $fontClasses = [
        'sans' => 'font-sans',
        'mono' => 'font-mono',
    ][$font];
@endphp

<x-table.td {{ $attributes->merge(['class' => $alignClasses]) }}>
    <span class="{{ $variantClasses }} {{ $sizeClasses }} {{ $fontClasses }}">
        {{ $slot }}
    </span>
</x-table.td>
