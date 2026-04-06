@props([
    'summary',
    'count' => null,
    'align' => 'left'
])
@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align];
    
    $tdClasses = $alignClasses . ' max-w-[250px] truncate';
@endphp

<x-table.td {{ $attributes->merge(['class' => $tdClasses, 'title' => $summary]) }}>
<span class="text-sm text-gray-300">
        {{ $summary }}
    </span>
    @if($count !== null)
        <span class="text-xs text-gray-500 ml-1">({{ $count }})</span>
    @endif
</x-table.td>
