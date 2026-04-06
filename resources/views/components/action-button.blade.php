@props(['href', 'icon', 'title', 'color' => 'gray'])

@php
    $colors = [
        'gray' => 'text-gray-300 hover:text-purple-400', // Better contrast and distinctive hover
        'red' => 'text-gray-500 hover:text-red-400',
        'blue' => 'text-blue-400 hover:text-white',
        'green' => 'text-emerald-400 hover:text-white',
        'yellow' => 'text-yellow-400 hover:text-white',
    ];

    $colorClass = $colors[$color] ?? $colors['gray'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "p-1 transition-colors duration-150 $colorClass"]) }}
    title="{{ $title }}">
    <i class="fas {{ $icon }}"></i>
</a>