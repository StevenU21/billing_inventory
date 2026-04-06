@props(['name' => 'search', 'label' => 'Buscar', 'placeholder' => 'Buscar...', 'value' => null])

<div class="flex flex-col sm:flex-row gap-2 items-end w-full">
    <div class="flex-1 w-full">
        <label for="{{ $name }}"
            class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
            {{ $label }}
        </label>
        <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ $value ?? request($name) }}"
            class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-300 px-3 py-2 h-[38px] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0"
            placeholder="{{ $placeholder }}">
    </div>
</div>