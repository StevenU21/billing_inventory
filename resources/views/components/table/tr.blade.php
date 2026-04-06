@props(['href' => null])

@if($href)
    <tr {{ $attributes->merge(['class' => 'hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-0 group cursor-pointer']) }}>
        {{ $slot }}
    </tr>
@else
    <tr {{ $attributes->merge(['class' => 'hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors border-b border-gray-100 dark:border-gray-800 last:border-0 group']) }}>
        {{ $slot }}
    </tr>
@endif