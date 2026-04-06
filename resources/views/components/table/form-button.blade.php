@props(['action', 'method' => 'POST', 'variant' => 'action', 'icon' => null, 'can' => null, 'canModel' => null, 'disabled' => false, 'confirm' => null, 'title' => ''])

@php
    $showButton = true;
    if ($can && $canModel) {
        $showButton = auth()->user()->can($can, $canModel);
    } elseif ($can) {
        $showButton = auth()->user()->can($can);
    }

    $variants = [
        'action' => 'inline-flex items-center justify-center h-9 w-9 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg focus:outline-none',
        'primary' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg focus:outline-none transition-colors',
        'secondary' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 rounded-lg focus:outline-none transition-colors',
        'danger' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-rose-700 bg-rose-100 hover:bg-rose-600 hover:text-white dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
        'warning' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-600 hover:text-white dark:bg-yellow-900/30 dark:text-yellow-200 dark:hover:bg-yellow-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
        'outline' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-purple-600 dark:text-purple-400 border border-purple-600 dark:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg focus:outline-none transition-colors',
        'indigo' => 'inline-flex items-center justify-center gap-2 h-9 px-3 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-600 hover:text-white dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-700 dark:hover:text-white rounded-lg focus:outline-none transition-colors',
    ];

    $classes = $variants[$variant] ?? $variants['action'];
@endphp

@if($showButton)
    <form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" class="inline-block">
        @if(strtoupper($method) !== 'GET')
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif
        @endif

        <button type="submit" title="{{ $title }}" {{ $attributes->merge(['class' => $classes]) }} 
            @if($disabled) disabled @endif
            @if($confirm) onclick="return confirm('{{ $confirm }}')" @endif>
            @if($icon)
                <i class="{{ $icon }}"></i>
            @endif
            {{ $slot }}
        </button>
    </form>
@endif