@props([
    'name',
    'label' => null,
    'icon' => null,
    'accept' => 'image/*',
    'required' => false,
    'previewId' => null,
    'previewSrc' => null,
    'previewShow' => false,
    'alpine' => false,
    'alpinePreviewSrc' => null,
])

@php
    $inputId = $name . '_input';
    $defaultPreviewId = $previewId ?? $name . '_preview';
@endphp

<label class="block text-sm w-full">
    @if($label)
        <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </span>
    @endif
    
    <div class="relative flex items-center text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400 mt-1">
        @if($icon)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="{{ $icon }} w-5 h-5"></i>
            </div>
        @endif
        
        <input 
            name="{{ $name }}" 
            type="file" 
            accept="{{ $accept }}"
            id="{{ $inputId }}"
            class="block w-full {{ $icon ? 'pl-10' : 'pl-3' }} text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error($name) border-red-600 @enderror"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        />
    </div>
    
    @if($previewId || $alpinePreviewSrc)
        <div class="mt-2">
            <img 
                id="{{ $defaultPreviewId }}"
                @if($alpine && $alpinePreviewSrc)
                    :src="{{ $alpinePreviewSrc }}"
                    x-show="{{ $alpinePreviewSrc }}"
                @else
                    src="{{ $previewSrc ?? '' }}"
                    style="display: {{ $previewShow ? 'block' : 'none' }};"
                @endif
                alt="Vista previa" 
                width="80" 
                class="rounded"
            >
        </div>
    @endif
    
    @error($name)
        <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
    @enderror
</label>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="file"]').forEach(input => {
                const previewId = input.id.replace('_input', '_preview');
                const preview = document.getElementById(previewId);
                
                if (preview) {
                    input.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(ev) {
                                preview.src = ev.target.result;
                                if (!preview.hasAttribute('x-show')) {
                                    preview.style.display = 'block';
                                }
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.src = '';
                            if (!preview.hasAttribute('x-show')) {
                                preview.style.display = 'none';
                            }
                        }
                    });
                }
            });
        });
    </script>
    @endpush
@endonce
