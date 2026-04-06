@props([
    'name' => null,
    'label' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'id' => null,
])
@php
    $checkboxId = $id ?? ($name ? $name . '_' . $value : uniqid('checkbox_'));
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <input 
     type="checkbox" 
    @if($name)
        name="{{ $name }}" 
    @endif
        id="{{ $checkboxId }}"
    value="{{ $value }}"

           {{ $checked ? 'checked' : '' }}
    {{ $disabled ? 'disabled' : '' }}
        class="w-5 h-5 text-purple-600 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-500 rounded focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 cursor-pointer transition-colors {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'hover:border-purple-400 dark:hover:border-purple-400' }}"
        {{ $attributes->except('class') }}
    />    
    @if($label || $slot->isNotEmpty())
        <label for="{{ $checkboxId }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 {{ $disabled ? 'opacity-60' : '' }}">
            {{ $label ?? $slot }}
        </label>
    @endif
</div>
