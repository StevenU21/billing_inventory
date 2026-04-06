@props([
    'cancelRoute' => null,
    'cancelText' => 'Cancelar',
    'submitText' => 'Guardar',
    'submitIcon' => 'fa-save',
    'align' => 'end', // 'start', 'center', 'end', 'between'
])
@php
    $alignmentClasses = [
        'start' => 'justify-start',
        'center' => 'justify-center',
        'end' => 'justify-end',
        'between' => 'justify-between',
    ];
    $alignClass = $alignmentClasses[$align] ?? $alignmentClasses['end'];
@endphp
<div {{ $attributes->merge(['class' => "flex gap-3 {$alignClass}"]) }}>
    @if($cancelRoute)
            <a href="{{ $cancelRoute }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 text-sm font-medium transition">
                {{ $cancelText }}
            </a>
    @endif

    @if(isset($prepend))
        {{ $prepend }}
    @endif

    <button type="submit"
        class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors flex items-center gap-2">
        @if($submitIcon)
            <i class="fas {{ $submitIcon }}"></i>
        @endif
        {{ $submitText }}
    </button>

    @if(isset($append))
        {{ $append }}
    @endif
</div>
