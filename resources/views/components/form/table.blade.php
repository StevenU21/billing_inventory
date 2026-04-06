@props([
    'title' => null,
    'icon' => null,
])

<div class="w-full overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
    @if($title)
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                @if($icon)
                    <i class="fas {{ $icon }} text-purple-500"></i>
                @endif
                {{ $title }}
            </h3>
            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    @endif

    <div class="w-full overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                <tr class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                    {{ $thead }}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-transparent">
                {{ $tbody }}
            </tbody>
        </table>
    </div>
</div>
