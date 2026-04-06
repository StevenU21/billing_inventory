@props([
    'title' => null,
    'icon' => null,
    'collapsible' => false,
    'collapsibleLabel' => 'Ver Detalles',
    'collapsibleLabelOpen' => 'Ocultar Detalles',
    'collapsibleIcon' => 'fa-info-circle',
])

<div {{ $attributes->merge(['class' => 'px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between']) }}>
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
        @if($icon)
            <i class="fas {{ $icon }} mr-2"></i>
        @endif
        {{ $title ?? $slot }}
    </h3>
    
    <div class="flex items-center gap-3">
        {{-- Custom Actions Slot --}}
        @if(isset($actions))
            {{ $actions }}
        @endif
        
        {{-- Collapsible Toggle Button --}}
        @if($collapsible)
            <button 
                @click="showPanel = !showPanel"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-150"
                type="button">
                <i class="fas {{ $collapsibleIcon }}"></i>
                <span x-text="showPanel ? '{{ $collapsibleLabelOpen }}' : '{{ $collapsibleLabel }}'"></span>
                <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': showPanel }"></i>
            </button>
        @endif
    </div>
</div>
