<style>
    @keyframes bell-ring {
        0% {
            transform: rotate(0deg);
        }

        10% {
            transform: rotate(12deg);
        }

        20% {
            transform: rotate(-10deg);
        }

        30% {
            transform: rotate(8deg);
        }

        40% {
            transform: rotate(-6deg);
        }

        50% {
            transform: rotate(4deg);
        }

        60% {
            transform: rotate(-2deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    .animate-bell {
        animation: bell-ring 1.4s ease-in-out infinite;
        transform-origin: top center;
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-bell {
            animation: none;
        }
    }
</style>

<li class="relative" x-data="notificationsBell({
    feedUrl: '{{ route('notifications.feed') }}',
    markAllUrl: '{{ route('notifications.markAll') }}',
    baseUrl: '{{ url('notifications') }}',
    indexUrl: '{{ route('notifications.index') }}'
})">
    <a href="{{ route('notifications.index') }}"
        class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple text-gray-600 dark:text-gray-300 inline-flex items-center justify-center"
        aria-label="Notificaciones">
        <i class="fas fa-bell w-5 h-5"></i>
        <span x-show="unreadCount > 0"
            class="absolute top-0 right-0 inline-flex min-w-[1.25rem] h-5 transform translate-x-1 -translate-y-1 bg-red-600 border-2 border-white rounded-full dark:border-gray-800 text-white text-[10px] font-semibold items-center justify-center leading-none px-1"
            aria-hidden="true" x-cloak x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
    </a>
</li>