@props([
    'nameFrom' => 'filter.from',
    'nameTo' => 'filter.to',
    'label' => 'Rango de Fechas',
    'labelFrom' => 'Desde',
    'labelTo' => 'Hasta',
    'valueFrom' => null,
    'valueTo' => null,
    'placeholderFrom' => 'Fecha inicial',
    'placeholderTo' => 'Fecha final',
    'required' => false,
    'min' => null,
    'max' => null,
])

@php
    $fromId = $attributes->get('id-from', $nameFrom);
    $toId = $attributes->get('id-to', $nameTo);
    $uniqueId = uniqid('range_');
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label class="block text-xs font-medium text-gray-400 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="grid grid-cols-2 gap-3" id="{{ $uniqueId }}">
        {{-- From Date --}}
        <div class="relative">
            @if($labelFrom)
                <label for="{{ $fromId }}" class="block text-xs font-medium text-gray-500 mb-1">
                    {{ $labelFrom }}
                </label>
            @endif
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-day text-gray-500 text-sm"></i>
                </div>
                
                <input 
                    datepicker
                    datepicker-format="yyyy-mm-dd"
                    datepicker-autohide
                    type="text" 
                    name="{{ $nameFrom }}" 
                    id="{{ $fromId }}"
                    value="{{ old($nameFrom, $valueFrom) }}"
                    {{ $min ? "datepicker-min-date={$min}" : '' }}
                    {{ $max ? "datepicker-max-date={$max}" : '' }}
                    {{ $required ? 'required' : '' }}
                    placeholder="{{ $placeholderFrom }}"
                    autocomplete="off"
                    class="block w-full pl-10 pr-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 transition-all h-[38px] cursor-pointer"
                />
            </div>
        </div>
        
        {{-- To Date --}}
        <div class="relative">
            @if($labelTo)
                <label for="{{ $toId }}" class="block text-xs font-medium text-gray-500 mb-1">
                    {{ $labelTo }}
                </label>
            @endif
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-check text-gray-500 text-sm"></i>
                </div>
                
                <input 
                    datepicker
                    datepicker-format="yyyy-mm-dd"
                    datepicker-autohide
                    type="text" 
                    name="{{ $nameTo }}" 
                    id="{{ $toId }}"
                    value="{{ old($nameTo, $valueTo) }}"
                    {{ $min ? "datepicker-min-date={$min}" : '' }}
                    {{ $max ? "datepicker-max-date={$max}" : '' }}
                    {{ $required ? 'required' : '' }}
                    placeholder="{{ $placeholderTo }}"
                    autocomplete="off"
                    class="block w-full pl-10 pr-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 transition-all h-[38px] cursor-pointer"
                />
            </div>
        </div>
    </div>
    
    @error($nameFrom)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
    
    @error($nameTo)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('{{ $uniqueId }}');
    if (!container) return;
    
    const fromInput = container.querySelector('#{{ $fromId }}');
    const toInput = container.querySelector('#{{ $toId }}');
    
    if (!fromInput || !toInput) return;
    
    // Initialize datepickers with existing values
    setTimeout(function() {
        // Set initial date for "from" input if it has a value
        if (fromInput.value && fromInput._datepicker) {
            const fromDate = new Date(fromInput.value);
            if (!isNaN(fromDate.getTime())) {
                fromInput._datepicker.setDate(fromDate);
            }
        }
        
        // Set initial date for "to" input if it has a value
        if (toInput.value && toInput._datepicker) {
            const toDate = new Date(toInput.value);
            if (!isNaN(toDate.getTime())) {
                toInput._datepicker.setDate(toDate);
            }
        }
        
        // Sync min/max dates if both have values
        if (fromInput.value && toInput._datepicker) {
            const fromDate = new Date(fromInput.value);
            if (!isNaN(fromDate.getTime())) {
                toInput._datepicker.setOptions({
                    minDate: fromDate
                });
            }
        }
        
        if (toInput.value && fromInput._datepicker) {
            const toDate = new Date(toInput.value);
            if (!isNaN(toDate.getTime())) {
                fromInput._datepicker.setOptions({
                    maxDate: toDate
                });
            }
        }
    }, 100);
    
    // Sync date ranges on change
    fromInput.addEventListener('changeDate', function(e) {
        if (e.detail && e.detail.date) {
            // Update "to" min date
            const toDatepicker = toInput._datepicker;
            if (toDatepicker) {
                toDatepicker.setOptions({
                    minDate: e.detail.date
                });
            }
        }
    });
    
    toInput.addEventListener('changeDate', function(e) {
        if (e.detail && e.detail.date) {
            // Update "from" max date
            const fromDatepicker = fromInput._datepicker;
            if (fromDatepicker) {
                fromDatepicker.setOptions({
                    maxDate: e.detail.date
                });
            }
        }
    });
});
</script>

<style>
    
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
