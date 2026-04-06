@props(['href', 'icon', 'title', 'active' => false, 'permission' => null])

@php
    $canAccess = true;
    if ($permission) {
        $canAccess = auth()->user()->can($permission);
    }
@endphp

@if($canAccess)
    <li class="relative px-6 py-3 {{ $active ? 'bg-gray-200/60 dark:bg-gray-900/30' : '' }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
        <span class="{{ $active ? 'absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg' : '' }}"
            aria-hidden="true"></span>
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $active ? 'text-gray-800 dark:text-gray-100' : '' }}"
            href="{{ $href }}">
            <i class="{{ $icon }} w-5 h-5"></i>
            <span class="ml-4" x-show="!sidebarCollapsed">{{ $title }}</span>
        </a>
        <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">{{ $title }}</span>
        </div>
    </li>
@endif
