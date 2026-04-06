@props([
    'name',
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'model' => null, // AlpineJS model binding (e.g., "attributes[index]")
    'suggestionsVar' => null, // Name of the AlpineJS variable containing suggestions (e.g., "availableAttributes")
    'suggestions' => [], // PHP array fallback
    'inputClass' => '',
])

<div class="relative"
     x-data="{ 
        open: false,
        get matches() {
            // Get source: either the JS variable name passed as string, or encoded PHP array
            let source = {{ $suggestionsVar ? $suggestionsVar : json_encode($suggestions) }};
            let currentVal = {{ $model }};
            
            if (!currentVal) return source;

            return source.filter(item => 
                item.toLowerCase().includes(currentVal.toLowerCase())
            );
        },
        select(val) {
            {{ $model }} = val;
            this.open = false;
        }
     }"
>
    @if($label)
        <label class="block text-xs font-medium text-gray-500 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    
    <input 
        type="text" 
        name="{{ $name }}"
        x-model="{{ $model }}"
        @focus="open = true"
        @click.outside="open = false"
        @keydown.escape="open = false"
        {{-- Close if Tab is pressed to move to next field --}}
        @keydown.tab="open = false" 
        placeholder="{{ $placeholder }}"
        class="block w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-700/50 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[38px] placeholder-gray-400 dark:placeholder-gray-500 {{ $inputClass }}"
        {{ $required ? 'required' : '' }}
        autocomplete="off"
    >

    {{-- Dropdown Suggestions --}}
    <div 
        x-show="open && matches.length > 0" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        style="display: none;"
    >
        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
            <template x-for="item in matches" :key="item">
                <li 
                    @click="select(item)" 
                    class="px-4 py-2 cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-600 dark:hover:text-purple-300 transition-colors"
                >
                    {{-- Highlight matching part? Keeping simplistic for now --}}
                    <span x-text="item"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
