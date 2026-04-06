@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'min' => null,
    'max' => null,
])

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="date" 
        name="{{ $name }}" 
        id="{{ $name }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
        {{ $min ? "min={$min}" : '' }}
        {{ $max ? "max={$max}" : '' }}
        class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px]"
    />
</div>
