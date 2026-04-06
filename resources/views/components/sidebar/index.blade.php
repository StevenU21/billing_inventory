@php
    $company = \App\Models\Company::first();
    $companyName = $company?->name ?? config('app.name');
    $words = preg_split('/\s+/', trim($companyName)) ?: [];
    $words = array_values(array_filter($words, static fn($w) => $w !== ''));
    $companyInitials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $companyInitials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    if ($companyInitials === '') {
        $companyInitials = mb_strtoupper(mb_substr($companyName, 0, 2));
    }
@endphp

<style>
    aside::-webkit-scrollbar {
        width: 6px;
        background: transparent;
    }

    aside::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.55);
        border-radius: 4px;
    }

    aside::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.75);
    }

    aside {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.55) transparent;
    }

    .dark aside::-webkit-scrollbar-thumb {
        background: rgba(107, 114, 128, 0.6);
    }

    .dark aside::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.8);
    }

    .dark aside {
        scrollbar-color: rgba(107, 114, 128, 0.6) transparent;
    }

    aside.collapsed::-webkit-scrollbar {
        width: 0;
    }

    /* Collapsed Sidebar Styles */
    aside.collapsed .ml-4 {
        display: none;
    }

    /* Force show text inside popovers */
    aside.collapsed .sidebar-popover .ml-4 {
        display: block !important;
    }

    aside.collapsed .fa-chevron-down,
    aside.collapsed .fa-chevron-up {
        display: none;
    }

    aside.collapsed .justify-between {
        justify-content: center;
    }

    aside.collapsed a.inline-flex,
    aside.collapsed button.inline-flex {
        justify-content: center;
    }

    aside.collapsed .px-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Tooltip & Popover Styles */
    aside.collapsed .group:hover .sidebar-tooltip {
        display: block;
    }

    aside.collapsed .group:hover>ul {
        display: block !important;
    }

    /* Invisible bridge for hover continuity */
    aside.collapsed .sidebar-popover::before {
        content: '';
        position: absolute;
        top: 0;
        left: -1.25rem;
        /* covers the ml-5 gap (approx 20px) to be safe */
        width: 1.25rem;
        height: 100%;
        background: transparent;
    }
</style>

<aside class="z-20 hidden bg-gray-100 dark:bg-gray-800 md:block flex-shrink-0 border-r border-gray-200 dark:border-gray-700
transition-all duration-300 flex flex-col"
    :class="sidebarCollapsed ? 'w-16 collapsed overflow-visible' : 'w-64 overflow-hidden'">
    <div class="py-4 text-gray-500 dark:text-gray-400 flex flex-col h-full">
        <a class="text-gray-800 dark:text-gray-200 flex items-center h-12 mb-4 px-4 relative" x-data="{ hover: false }"
            @mouseenter="hover = true" @mouseleave="hover = false"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" href="{{ route('dashboard.index') }}">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-purple-600 text-white font-bold">
                {{ $companyInitials }}
            </div>
            <div class="ml-3 min-w-0" x-show="!sidebarCollapsed">
                <div class="text-base font-bold truncate">{{ $companyName }}</div>
            </div>

            <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
                <span
                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">{{ $companyName }}</span>
            </div>
        </a>

        <div class="flex-1" :class="sidebarCollapsed ? 'overflow-visible' : 'overflow-y-auto'">
            <ul class="mt-6">
                {{ $slot }}
            </ul>
        </div>

        @auth
            @php
                /** @var \App\Models\User $user */
                $user = auth()->user();
            @endphp

            <div class="mt-4 border-t border-gray-200/70 dark:border-gray-700/70 pt-3 pb-4">
                <div class="px-6" x-show="!sidebarCollapsed" x-cloak>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Utilidades
                    </div>
                </div>

                <ul class="mt-2">
                    @if ($user->can('read backups'))
                        <li class="relative px-6 py-3" x-data="{ hover: false }" @mouseenter="hover = true"
                            @mouseleave="hover = false">
                            <a class="inline-flex items-center w-full text-sm font-medium transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                href="{{ route('backups.index') }}">
                                <i class="fas fa-database w-5 h-5"></i>
                                <span class="ml-4" x-show="!sidebarCollapsed">Respaldos</span>
                            </a>
                            <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
                                <span
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Respaldos</span>
                            </div>
                        </li>
                    @endif

                    @if ($user->can('read notifications'))
                        <li class="relative px-6 py-3" 
                            x-data="{ 
                                ...notificationsBell({
                                    feedUrl: '{{ route('notifications.feed') }}',
                                    baseUrl: '{{ url('/') }}'
                                }),
                                hover: false 
                            }" 
                            @mouseenter="hover = true" 
                            @mouseleave="hover = false">
                            <a class="inline-flex items-center w-full text-sm font-medium transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                href="{{ route('notifications.index') }}">
                                <div class="relative">
                                    <i class="fas fa-bell w-5 h-5 transition-transform duration-300"
                                        :class="{ 'animate-swing': hover }"></i>
                                    <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-50"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-gray-900">
                                    </span>
                                </div>
                                <span class="ml-4" x-show="!sidebarCollapsed">Notificaciones</span>
                            </a>
                            <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
                                <span
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Notificaciones</span>
                            </div>
                        </li>
                    @endif

                    @if ($user->can('read settings'))
                        <li class="relative px-6 py-3" x-data="{ hover: false }" @mouseenter="hover = true"
                            @mouseleave="hover = false">
                            <a class="inline-flex items-center w-full text-sm font-medium transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                href="{{ route('settings.index') }}">
                                <i class="fas fa-sliders-h w-5 h-5"></i>
                                <span class="ml-4" x-show="!sidebarCollapsed">Configuración</span>
                            </a>
                            <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
                                <span
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Configuración</span>
                            </div>
                        </li>
                    @endif

                    @if ($user->can('read updates'))
                        <li class="relative px-6 py-3" x-data="{ hover: false }" @mouseenter="hover = true"
                            @mouseleave="hover = false">
                            <a class="inline-flex items-center w-full text-sm font-medium transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                href="{{ route('native-app.updates.index') }}">
                                <i class="fas fa-sync-alt w-5 h-5"></i>
                                <span class="ml-4" x-show="!sidebarCollapsed">Actualizaciones</span>
                            </a>
                            <div x-show="sidebarCollapsed && hover" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute left-full top-0 ml-5 px-3 py-2 bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 whitespace-nowrap w-max">
                                <span
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Actualizaciones</span>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        @endauth
    </div>
</aside>