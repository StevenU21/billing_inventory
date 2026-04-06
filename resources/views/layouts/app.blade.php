<!DOCTYPE html>
<html x-data="data" lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @yield('title') - {{ config('app.name') }}
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Hide Alpine elements until it initializes */
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('theme') || 'system';
                var isDark = false;
                if (theme === 'dark') {
                    isDark = true;
                } else if (theme === 'light') {
                    isDark = false;
                } else if (theme === 'system') {
                    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
                if (isDark) {
                    document.documentElement.classList.add('dark', 'theme-dark');
                } else {
                    document.documentElement.classList.remove('dark', 'theme-dark');
                }
            } catch (e) {
                console.error('Error applying theme:', e);
            }
        })();
    </script>
</head>

<body>
    <!-- Loading Screen -->
    <div id="app-loader"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900 transition-opacity duration-300">
        <div class="text-center">
            <div class="h-12 w-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto">
            </div>
            <p class="mt-4 text-sm font-medium text-gray-600 dark:text-gray-300">Cargando...</p>
        </div>
    </div>

    <div class="flex h-screen bg-gray-200 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <!-- Desktop sidebar -->
        @include('navigations.sidebar')

        <div class="flex flex-col flex-1 w-full min-w-0">
            @include('navigations.header')
            <main class="h-full overflow-y-auto overflow-x-hidden">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        // Loader logic is now handled in resources/js/loader.js
    </script>
    {{-- Stack for page-specific scripts (e.g., dashboard charts) --}}
    @stack('scripts')
</body>

</html>