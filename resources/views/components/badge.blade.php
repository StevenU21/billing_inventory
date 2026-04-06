@props(['color' => 'gray', 'text' => ''])

@php
    $colors = [
        'green' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
        'red' => 'bg-red-500/10 text-red-500 border border-red-500/20',
        'blue' => 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
        'gray' => 'bg-gray-500/10 text-gray-500 border border-gray-500/20',
        'yellow' => 'bg-orange-500/10 text-orange-500 border border-orange-500/20',
    ];
    $classes = $colors[$color] ?? $colors['gray'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $text }}
</span>