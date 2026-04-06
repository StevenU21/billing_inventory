@props(['image' => null, 'alt' => '', 'initials' => '?'])

<td class="px-4 py-3 text-sm">
    @if ($image)
        <img src="{{ $image }}" alt="{{ $alt }}"
            class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-700">
    @else
        <div
            class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300 text-xs font-bold">
            {{ $initials }}
        </div>
    @endif
</td>