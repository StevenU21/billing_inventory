@props([
    'name',
    'label' => null,
    'icon' => null,
    'placeholder' => 'Seleccionar...',
    'required' => false,
    'alpine' => false,
    'alpineModel' => null,
    'selected' => null,
])

<label class="block text-sm w-full">
    @if($label)
        <span class="text-gray-700 dark:text-gray-400">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </span>
    @endif
    
    <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
        <select 
            name="{{ $name }}"
            id="{{ $name }}"
            class="block w-full {{ $icon ? 'pl-10' : 'pl-3' }} mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error($name) border-red-600 @enderror"
            @if($alpine && $alpineModel) x-model="{{ $alpineModel }}" @endif
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        
        @if($icon)
            <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                <i class="{{ $icon }} w-5 h-5"></i>
            </div>
        @endif
    </div>
    
    @error($name)
        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
    @enderror
</label>
