@props([
    'cols' => 4, // Número de columnas en desktop: 1, 2, 3, 4, 5, 6
])

@php
    $colsClasses = [
        1 => 'lg:grid-cols-1',
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        4 => 'lg:grid-cols-4',
        5 => 'lg:grid-cols-5',
        6 => 'lg:grid-cols-6',
    ][$cols] ?? 'lg:grid-cols-4';
@endphp

<div 
    x-show="showPanel" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    {{ $attributes->merge(['class' => 'border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50']) }}
    style="display: none;">
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 {{ $colsClasses }} gap-4">
        {{ $slot }}
    </div>
</div>
