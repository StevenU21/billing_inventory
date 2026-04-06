@props([
    'top' => null,           // Texto principal (arriba)
    'middle' => null,        // Texto secundario (medio)
    'bottom' => null,        // Texto terciario (abajo)
    'topClass' => '',        // Clases extra para top
    'middleClass' => '',     // Clases extra para middle
    'bottomClass' => '',     // Clases extra para bottom
    'align' => 'left',       // left, center, right
    'route' => null,         // Si el top debe ser un link
])

@php
    $alignClasses = [
        'left' => 'items-start text-left',
        'center' => 'items-center text-center',
        'right' => 'items-end text-right',
    ][$align];
@endphp

<x-table.td {{ $attributes->merge(['class' => 'overflow-hidden']) }}>
    <div class="flex flex-col {{ $alignClasses }} gap-0.5">
        {{-- Nivel Principal --}}
        @if ($top)
            <div class="font-medium text-gray-800 dark:text-gray-200 text-sm {{ $topClass }} truncate max-w-full">
                @if ($route)
                    <a href="{{ $route }}" class="hover:underline hover:text-indigo-400 transition-colors">
                        {{ $top }}
                    </a>
                @else
                    {{ $top }}
                @endif
            </div>
        @endif

        {{-- Nivel Secundario --}}
        @if ($middle)
            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal {{ $middleClass }}">
                {{ $middle }}
            </div>
        @endif

        {{-- Nivel Terciario --}}
        @if ($bottom)
            <div class="text-[11px] text-gray-400 dark:text-gray-500 font-normal leading-tight {{ $bottomClass }}">
                {{ $bottom }}
            </div>
        @endif

        {{-- Slot para contenido personalizado extra --}}
        {{ $slot }}
    </div>
</x-table.td>
