@props(['action', 'method' => 'POST', 'target' => null, 'icon' => null, 'can' => null, 'canModel' => null, 'disabled' => false, 'confirm' => null])

@php
    $showButton = true;
    if ($can && $canModel) {
        $showButton = auth()->user()->can($can, $canModel);
    } elseif ($can) {
        $showButton = auth()->user()->can($can);
    }
@endphp

@if($showButton)
    <form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" @if($target)
    target="{{ $target }}" @endif class="flex">
        @if(strtoupper($method) !== 'GET')
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif
        @endif

        <button type="submit" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 text-sm font-medium transition']) }} @if($disabled) disabled @endif @if($confirm)
        onclick="return confirm('{{ $confirm }}')" @endif>
            @if($icon)
                <i class="{{ $icon }}"></i>
            @endif
            {{ $slot }}
        </button>
    </form>
@endif