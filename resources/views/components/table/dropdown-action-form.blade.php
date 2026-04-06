@props([
    'action', 
    'method' => 'POST',
    'message' => null, 
    'title',
    'icon',
    'color' => 'gray',
    'can' => null
])

@php
    $colors = [
        'gray' => 'text-gray-200 hover:bg-gray-700/60',
        'red' => 'text-red-400 hover:bg-red-500/10',
        'blue' => 'text-blue-400 hover:bg-blue-500/10',
        'green' => 'text-emerald-400 hover:bg-emerald-500/10',
        'emerald' => 'text-emerald-400 hover:bg-emerald-500/10',
        'purple' => 'text-purple-400 hover:bg-purple-500/10',
    ];

    $colorClass = $colors[$color] ?? $colors['gray'];
@endphp

@if(is_null($can))
    @php $show = true; @endphp
@elseif(is_array($can))
    @php $show = auth()->user()?->can($can[0], $can[1] ?? []); @endphp
@else
    @php $show = auth()->user()?->can($can); @endphp
@endif

@if($show)
    <form action="{{ $action }}" method="POST" @if($message) onsubmit="return confirm('{{ $message }}');" @endif class="block w-full">
        @csrf
        @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif
        <button type="submit"
            class="w-full text-left px-4 py-2.5 text-sm transition-colors rounded-md {{ $colorClass }} flex items-center gap-3"
            role="menuitem">
            <i class="fas {{ $icon }} w-4"></i>
            <span>{{ $title }}</span>
        </button>
        {{ $slot }}
    </form>
@endif
