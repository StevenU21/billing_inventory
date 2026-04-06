@props(['href', 'icon', 'color' => 'gray', 'can' => null])

@php
    $colors = [
        'gray' => 'text-gray-200 hover:bg-gray-700/60',
        'red' => 'text-red-400 hover:bg-red-500/10',
        'blue' => 'text-blue-400 hover:bg-blue-500/10',
        'green' => 'text-emerald-400 hover:bg-emerald-500/10',
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
    <a href="{{ $href }}" data-no-loader="true"
        class="block px-4 py-2.5 text-sm transition-colors rounded-md {{ $colorClass }} flex items-center gap-3"
        role="menuitem" {{ $attributes }}>
        <i class="fas {{ $icon }} w-4"></i>
        <span>{{ $slot }}</span>
    </a>
@endif