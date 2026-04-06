@props([
    'name',
    'label' => null,
    'value',
    'checked' => false,
    'disabled' => false,
])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <input 
        type="radio" 
        name="{{ $name }}" 
        id="{{ $name }}_{{ $value }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="w-4 h-4 text-purple-600 bg-gray-800 border-gray-700 focus:ring-purple-500 focus:ring-2 {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }}"
    />
    
    @if($label)
        <label for="{{ $name }}_{{ $value }}" class="ml-2 text-sm font-medium text-gray-300 {{ $disabled ? 'opacity-60' : '' }}">
            {{ $label }}
        </label>
    @endif
</div>
