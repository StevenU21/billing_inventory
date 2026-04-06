@props([
    'nameMin' => 'filter.min',
    'nameMax' => 'filter.max',
    'label' => 'Rango',
    'labelMin' => 'Mín',
    'labelMax' => 'Máx',
    'valueMin' => null,
    'valueMax' => null,
    'placeholderMin' => '0',
    'placeholderMax' => '999',
    'step' => '0.01',
    'min' => '0',
    'max' => null,
    'icon' => 'fa-dollar-sign',
    'prefix' => null,
])

@php
    $minId = str_replace(['[', ']', '.'], '_', $nameMin);
    $maxId = str_replace(['[', ']', '.'], '_', $nameMax);
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
        </label>
    @endif
    
    <div class="grid grid-cols-2 gap-2">
        {{-- Min Value --}}
        <div class="relative">
            @if($labelMin)
                <label for="{{ $minId }}" class="block text-xs font-medium text-gray-500 mb-1">
                    {{ $labelMin }}
                </label>
            @endif
            
            <div class="relative">
                @if($icon || $prefix)
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        @if($prefix)
                            <span class="text-gray-500 text-xs font-medium">{{ $prefix }}</span>
                        @else
                            <i class="fas {{ $icon }} text-gray-500 text-xs"></i>
                        @endif
                    </div>
                @endif
                
                <input 
                    type="number" 
                    name="{{ $nameMin }}" 
                    id="{{ $minId }}"
                    value="{{ old($nameMin, $valueMin) }}"
                    step="{{ $step }}"
                    min="{{ $min }}"
                    @if($max) max="{{ $max }}" @endif
                    placeholder="{{ $placeholderMin }}"
                    class="block w-full {{ $icon || $prefix ? 'pl-8' : 'pl-3' }} pr-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 transition-all h-[38px] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                />
            </div>
        </div>
        
        {{-- Max Value --}}
        <div class="relative">
            @if($labelMax)
                <label for="{{ $maxId }}" class="block text-xs font-medium text-gray-500 mb-1">
                    {{ $labelMax }}
                </label>
            @endif
            
            <div class="relative">
                @if($icon || $prefix)
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        @if($prefix)
                            <span class="text-gray-500 text-xs font-medium">{{ $prefix }}</span>
                        @else
                            <i class="fas {{ $icon }} text-gray-500 text-xs"></i>
                        @endif
                    </div>
                @endif
                
                <input 
                    type="number" 
                    name="{{ $nameMax }}" 
                    id="{{ $maxId }}"
                    value="{{ old($nameMax, $valueMax) }}"
                    step="{{ $step }}"
                    min="{{ $min }}"
                    @if($max) max="{{ $max }}" @endif
                    placeholder="{{ $placeholderMax }}"
                    class="block w-full {{ $icon || $prefix ? 'pl-8' : 'pl-3' }} pr-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 transition-all h-[38px] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                />
            </div>
        </div>
    </div>
    
    @error($nameMin)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
    
    @error($nameMax)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
