@props(['href', 'icon' => null, 'title', 'active' => false, 'permission' => null])

@php
    $canAccess = true;
    if ($permission) {
        $canAccess = auth()->user()->can($permission);
    }
@endphp

@if($canAccess)
    <li class="py-2 relative {{ $active ? 'bg-gray-200/60 dark:bg-gray-900/30' : '' }}">
        <span class="{{ $active ? 'absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg' : '' }}"
            aria-hidden="true"></span>
        <a href="{{ $href }}" :class="sidebarCollapsed ? 'px-3 font-semibold' : 'pl-9 pr-3 font-normal'"
            class="group/item flex items-center justify-start text-left w-full transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 text-sm {{ $active ? 'text-gray-800 dark:text-gray-100' : 'text-gray-500/80 dark:text-gray-400/80' }} relative">

            {{-- Curved Branch (YouTube style) --}}
            <div x-show="!sidebarCollapsed"
                class="absolute left-0 top-0 h-[50%] w-4 border-b-[2px] border-l-[2px] rounded-bl-lg border-gray-200 dark:border-gray-700 pointer-events-none {{ $active ? 'border-purple-500 dark:border-purple-500' : 'group-hover/item:border-purple-400/50' }} transition-colors">
            </div>

            @if($icon)
                <i class="{{ $icon }} w-5 text-center mr-3" x-show="sidebarCollapsed"></i>
            @endif

            <span>{{ $title }}</span>
        </a>
    </li>
@endif