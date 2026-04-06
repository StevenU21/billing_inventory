@props([
    'href' => '#',
    'variant' => 'primary',
    'icon' => null,
    'title' => '',
    'can' => null,
    'canModel' => null,
])
@php
    $showLink = true;
    if ($can && $canModel) {
        $showLink = auth()->user()->can($can, $canModel);
    } elseif ($can) {
        $showLink = auth()->user()->can($can);
    }

    $variants = [
        'action' => 'inline-flex items-center justify-center h-9 w-9 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg focus:outline-none transition-colors',
        'primary' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg focus:outline-none transition-colors shadow-lg shadow-purple-900/50',
        'secondary' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 rounded-lg focus:outline-none transition-colors border border-gray-300 dark:border-gray-600',
        'danger' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-rose-700 bg-rose-100 hover:bg-rose-600 hover:text-white dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
        'warning' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-600 hover:text-white dark:bg-yellow-900/30 dark:text-yellow-200 dark:hover:bg-yellow-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
        'outline' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-purple-600 dark:text-purple-400 border border-purple-600 dark:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg focus:outline-none transition-colors',
        'indigo' => 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-600 hover:text-white dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
    ];

    $classes = $variants[$variant] ?? $variants['primary'];
@endphp
@if($showLink)
    <a href="{{ $href }}" title="{{ $title }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@endif
