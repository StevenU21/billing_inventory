@props(['icon' => null])

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider']) }}>
    @if($icon)
        <i class="fas {{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</th>