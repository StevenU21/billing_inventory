@props(['label', 'icon', 'active' => false, 'permissions' => null])

@php
    $canAccess = false;
    if (is_array($permissions)) {
        foreach ($permissions as $p) {
            if (auth()->user()->can($p)) {
                $canAccess = true;
                break;
            }
        }
    } elseif ($permissions) {
        $canAccess = auth()->user()->can($permissions);
    } else {
        $canAccess = true;
    }
@endphp

@if($canAccess)
    <li class="relative px-6 py-3 group {{ $active ? 'bg-gray-200/60 dark:bg-gray-900/30' : '' }}"
        x-data="{ isOpen: false }" @mouseenter="if (sidebarCollapsed) isOpen = true"
        @mouseleave="if (sidebarCollapsed) isOpen = false">
        <span class="{{ $active ? 'absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg' : '' }}"
            aria-hidden="true"></span>
        <button
            class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 focus:outline-none overflow-hidden"
            @click="if (!sidebarCollapsed) isOpen = !isOpen" aria-haspopup="true">
            <span class="inline-flex items-center truncate mr-2">
                <i class="{{ $icon }} w-5 h-5 flex-shrink-0"></i>
                <span class="ml-4 truncate" x-show="!sidebarCollapsed">{{ $label }}</span>
            </span>
            <i class="fas flex-shrink-0" :class="{ 'fa-chevron-down': !isOpen, 'fa-chevron-up': isOpen }"
                x-show="!sidebarCollapsed"></i>
        </button>
        <!-- Tooltip -->
        <div class="sidebar-tooltip hidden absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max"
            x-show="sidebarCollapsed">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">{{ $label }}</span>
        </div>
        <!-- Menu -->
        <ul x-show="isOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            :class="sidebarCollapsed ?
                        'absolute left-full top-10 ml-5 w-56 sidebar-popover bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 p-2 text-left' :
                        'mt-0 px-0 overflow-hidden text-sm font-medium text-gray-600 dark:text-gray-300 relative ml-[33px] border-l border-gray-200 dark:border-gray-700'">
            {{ $slot }}
        </ul>
    </li>
@endif