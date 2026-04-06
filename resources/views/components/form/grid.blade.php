@props([
    'cols' => 1, // Number of columns on mobile
    'md' => 2,   // Number of columns on medium screens
    'lg' => 3,   // Number of columns on large screens
    'gap' => 4,  // Gap between items
])

@php
    $gridClass = "grid grid-cols-{$cols} md:grid-cols-{$md} lg:grid-cols-{$lg} gap-{$gap}";
@endphp

<div {{ $attributes->merge(['class' => $gridClass]) }}>
    {{ $slot }}
</div>
