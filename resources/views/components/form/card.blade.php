@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700']) }}>
    @if($title)
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                @if($icon)
                    <i class="fas {{ $icon }} text-purple-500"></i>
                @endif
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
