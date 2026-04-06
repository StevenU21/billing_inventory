@props(['action'])

<div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
    <form action="{{ $action }}" {{ $attributes->merge(['method' => 'GET', 'class' => 'grid grid-cols-12 gap-4 items-end']) }}>
        {{ $slot }}
    </form>
</div>