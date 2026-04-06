@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => 'Seleccionar fecha',
    'required' => false,
    'min' => null,
    'max' => null,
    'disabled' => false,
    'readonly' => false,
])

@php
    $inputId = $attributes->get('id', $name);
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-500 text-sm"></i>
        </div>
        
        <input 
            datepicker
            datepicker-format="yyyy-mm-dd"
            type="text" 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $min ? "datepicker-min-date={$min}" : '' }}
            {{ $max ? "datepicker-max-date={$max}" : '' }}
            autocomplete="off"
            class="block w-full pl-10 pr-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 transition-all h-[38px] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        />
    </div>
    
    @error($name)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('{{ $inputId }}');
    if (!input) return;
    
    // Initialize datepicker with existing value
    setTimeout(function() {
        if (input.value && input._datepicker) {
            const date = new Date(input.value);
            if (!isNaN(date.getTime())) {
                input._datepicker.setDate(date);
            }
        }
    }, 100);
});
</script>

<style>
/* Flowbite Datepicker Theme Customization - Light & Dark Mode */

/* Light Mode Styles */
.datepicker-picker {
    @apply bg-white border border-gray-300 shadow-2xl;
}

.datepicker-header {
    @apply bg-gray-50 border-b border-gray-300;
}

.datepicker-title {
    @apply text-gray-900 font-semibold;
}

.datepicker-controls .button {
    @apply text-gray-600 hover:bg-gray-200 hover:text-gray-900 rounded-lg transition-colors;
}

.datepicker-view {
    @apply bg-white;
}

.datepicker-view .days {
    @apply bg-white;
}

.datepicker-view .dow {
    @apply text-gray-600 font-semibold uppercase text-xs;
}

.datepicker-cell {
    @apply text-gray-700 hover:bg-gray-100 rounded-lg transition-colors;
}

.datepicker-cell.selected {
    @apply bg-purple-600 text-white hover:bg-purple-700 font-semibold;
}

.datepicker-cell.focused {
    @apply bg-gray-100;
}

.datepicker-cell.today {
    @apply bg-purple-100 text-purple-700 font-semibold;
}

.datepicker-cell.today.selected {
    @apply bg-purple-600 text-white;
}

.datepicker-cell.disabled {
    @apply text-gray-400 cursor-not-allowed opacity-50;
}

.datepicker-cell.prev, 
.datepicker-cell.next {
    @apply text-gray-400 opacity-60;
}

.datepicker-cell.range-start,
.datepicker-cell.range-end {
    @apply bg-purple-600 text-white font-semibold;
}

.datepicker-cell.range {
    @apply bg-purple-50;
}

/* Dark Mode Styles - Using Windmill Theme Colors */
.dark .datepicker-picker {
    background-color: #24262d !important;
    border: 1px solid #4c4f52 !important;
}

.dark .datepicker-header {
    background-color: #24262d !important;
    border-bottom: 1px solid #4c4f52 !important;
}

.dark .datepicker-title {
    color: #e5e7eb !important;
    font-weight: 600;
}

.dark .datepicker-controls {
    color: #e5e7eb !important;
}

.dark .datepicker-controls .button {
    color: #9e9e9e !important;
}

.dark .datepicker-controls .button:hover {
    background-color: #1a1c23 !important;
    color: #e5e7eb !important;
}

.dark .datepicker-controls .view-switch {
    color: #e5e7eb !important;
}

.dark .datepicker-controls .prev-btn,
.dark .datepicker-controls .next-btn {
    color: #9e9e9e !important;
}

.dark .datepicker-controls .prev-btn:hover,
.dark .datepicker-controls .next-btn:hover {
    background-color: #1a1c23 !important;
    color: #e5e7eb !important;
}

.dark .datepicker-view {
    background-color: #24262d !important;
}

.dark .datepicker-view .days {
    background-color: #24262d !important;
}

.dark .datepicker-view .dow {
    color: #707275 !important;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
}

.dark .datepicker-cell {
    color: #d5d6d7 !important;
}

.dark .datepicker-cell:hover {
    background-color: #1a1c23 !important;
}

.dark .datepicker-cell.selected {
    background-color: #7e3af2 !important;
    color: #ffffff !important;
    font-weight: 600;
}

.dark .datepicker-cell.selected:hover {
    background-color: #6c2bd9 !important;
}

.dark .datepicker-cell.focused {
    background-color: #1a1c23 !important;
}

.dark .datepicker-cell.today {
    background-color: rgba(126, 58, 242, 0.2) !important;
    color: #bda5f3 !important;
    font-weight: 600;
}

.dark .datepicker-cell.today.selected {
    background-color: #7e3af2 !important;
    color: #ffffff !important;
}

.dark .datepicker-cell.disabled {
    color: #4c4f52 !important;
    opacity: 0.5;
}

.dark .datepicker-cell.prev, 
.dark .datepicker-cell.next {
    color: #4c4f52 !important;
    opacity: 0.6;
}

.dark .datepicker-cell.range-start,
.dark .datepicker-cell.range-end {
    background-color: #7e3af2 !important;
    color: #ffffff !important;
    font-weight: 600;
}

.dark .datepicker-cell.range {
    background-color: rgba(126, 58, 242, 0.1) !important;
}

/* Dropdown positioning */
.datepicker-dropdown {
    z-index: 9999 !important;
}
</style>
