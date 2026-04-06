@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Seleccionar...',
    'required' => false,
    'searchPlaceholder' => 'Buscar...',
    'modelKey' => null,      // For dynamic x-for usage: 'item.product_variant_id'
    'dynamicName' => null,   // For dynamic x-for usage: "'items[' + index + '][product_variant_id]'"
])

@php
    $isDynamic = $modelKey !== null;
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        dropdownStyle: '',
        @if($isDynamic)
        get selected() {
            return {{ $modelKey }} || '';
        },
        set selected(value) {
            {{ $modelKey }} = value;
        },
        @else
        selected: '{{ old($name, $selected) }}',
        @endif
        selectedLabel: '',
        options: {{ json_encode($options) }},
        
        get filteredOptions() {
            if (!this.search) return this.options;
            
            const searchLower = this.search.toLowerCase();
            return Object.fromEntries(
                Object.entries(this.options).filter(([value, label]) => {
                    const labelStr = String(label).toLowerCase();
                    const valueStr = String(value).toLowerCase();
                    return labelStr.includes(searchLower) || valueStr.includes(searchLower);
                })
            );
        },
        
        selectOption(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
        },
        
        updateDropdownPosition() {
            const button = this.$refs.button;
            if (!button) return;
            const rect = button.getBoundingClientRect();
            const dropdownHeight = 280; // max-h-[280px]
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            
            // Open upward if not enough space below and more space above
            if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                this.dropdownStyle = `position: fixed; bottom: ${window.innerHeight - rect.top + 4}px; left: ${rect.left}px; width: ${rect.width}px; max-height: ${Math.min(dropdownHeight, spaceAbove - 8)}px;`;
            } else {
                this.dropdownStyle = `position: fixed; top: ${rect.bottom + 4}px; left: ${rect.left}px; width: ${rect.width}px; max-height: ${Math.min(dropdownHeight, spaceBelow - 8)}px;`;
            }
        },
        
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.updateDropdownPosition());
            }
        },
        
        init() {
            @if($isDynamic)
            // Watch for external changes to sync the label
            this.$watch('selected', (value) => {
                this.selectedLabel = value && this.options[value] ? this.options[value] : '';
            });
            @endif
            if (this.selected && this.options[this.selected]) {
                this.selectedLabel = this.options[this.selected];
            }
        }
    }"
    @click.away="open = false"
    @scroll.window="if(open) updateDropdownPosition()"
    @resize.window="if(open) updateDropdownPosition()"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Hidden input to store the actual value --}}
    @if($isDynamic && $dynamicName)
        <input type="hidden" :name="{{ $dynamicName }}" x-model="selected" {{ $required ? 'required' : '' }}>
    @else
        <input type="hidden" name="{{ $name }}" x-model="selected" {{ $required ? 'required' : '' }}>
    @endif

    {{-- Custom Select Button --}}
    <button
        type="button"
        x-ref="button"
        @click="toggle()"
        class="block w-full mt-1 text-sm text-left text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px] px-3 flex items-center justify-between cursor-pointer transition-colors hover:border-gray-400 dark:hover:border-gray-600"
        :class="{ 'ring-1 ring-purple-400 border-purple-400': open }"
    >
        <span x-text="selectedLabel || '{{ $placeholder }}'" :class="{ 'text-gray-400 dark:text-gray-500': !selectedLabel }"></span>
        <i class="fas fa-chevron-down text-xs transition-transform text-gray-400" :class="{ 'rotate-180': open }"></i>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg flex flex-col"
        :style="dropdownStyle"
        style="display: none;"
    >
        {{-- Search Input --}}
        <div class="p-2 border-b border-gray-200 dark:border-gray-700">
            <div class="relative">
                <input
                    type="text"
                    x-model="search"
                    @click.stop
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full pl-8 pr-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400"
                >
                <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
        </div>

        {{-- Options List --}}
        <div class="flex-1 overflow-y-auto">
            @if($placeholder)
                <div
                    @click="selectOption('', '{{ $placeholder }}')"
                    class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-300 cursor-pointer transition-colors"
                    :class="{ 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-300 font-medium': selected === '' }"
                >
                    {{ $placeholder }}
                </div>
            @endif

            <template x-for="[value, label] in Object.entries(filteredOptions)" :key="value">
                <div
                    @click="selectOption(value, label)"
                    class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-300 cursor-pointer transition-colors flex items-center justify-between"
                    :class="{ 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-300 font-medium': selected === value }"
                >
                    <span x-text="label"></span>
                    <i x-show="selected === value" class="fas fa-check text-purple-600 dark:text-purple-400 text-xs"></i>
                </div>
            </template>

            <div 
                x-show="Object.keys(filteredOptions).length === 0" 
                class="px-3 py-8 text-sm text-center text-gray-400"
            >
                <i class="fas fa-search text-2xl mb-2 block"></i>
                <p>No se encontraron resultados</p>
            </div>
        </div>
    </div>
</div>
