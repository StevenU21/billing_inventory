@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
    'actionHref' => null,
    'actionLabel' => null,
    'actionPermission' => null,
    'backUrl' => null,
])

<div class="flex flex-col gap-6 mb-8 mt-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-3">
                @if($icon)
                    <i class="fas {{ $icon }} text-gray-500 text-xl hidden sm:inline-block"></i>
                @endif
                {{ $title }}
                
                @if(isset($badges))
                    {{ $badges }}
                @endif
            </h2>
            @if($subtitle)
                <p class="text-sm text-gray-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        
        <div class="flex gap-3">
            @if(isset($actions))
                {{ $actions }}
            @endif
            
            {{ $slot }}

            @if($actionHref && $actionLabel)
                @if($actionPermission)
                    @can($actionPermission)
                        <a href="{{ $actionHref }}"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-lg shadow-purple-900/50 inline-flex items-center gap-2">
                            <i class="fas fa-plus"></i>
                            {{ $actionLabel }}
                        </a>
                    @endcan
                @else
                    <a href="{{ $actionHref }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors shadow-lg shadow-purple-900/50 inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        {{ $actionLabel }}
                    </a>
                @endif
            @endif
            
            @if($backUrl)
                <a href="{{ $backUrl }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>
</div>
