<div
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden']) }}
    x-data="{ tab: '{{ $default ?? 'perfil' }}' }"
>
    <div class="px-6 pt-4 border-b border-gray-100 dark:border-gray-700">
        <div class="flex gap-6 text-sm">
            {{ $tabs ?? '' }}
        </div>
    </div>

    {{ $slot }}
</div>
