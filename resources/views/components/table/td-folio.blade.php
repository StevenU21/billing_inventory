@props(['id', 'padding' => 6, 'bottom' => null])

<x-table.td class="whitespace-nowrap group">
    <div class="flex flex-col">
        <span class="font-mono text-sm text-gray-500 group-hover:text-indigo-400 transition-colors">
            #{{ str_pad($id, $padding, '0', STR_PAD_LEFT) }}
        </span>
        @if ($bottom)
            <span class="text-xs text-gray-400 font-normal">
                {{ $bottom }}
            </span>
        @endif
    </div>
</x-table.td>