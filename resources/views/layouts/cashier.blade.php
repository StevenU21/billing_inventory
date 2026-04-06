<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="es" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @yield('title') - {{ config('app.name') }}
    </title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

<body class="min-h-screen flex flex-col">
    <!-- Loading Screen -->
    <div id="app-loader"
        class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-gray-900 transition-opacity duration-300">
        <div class="text-center">
            <div class="h-12 w-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto">
            </div>
            <p class="mt-4 text-sm font-medium text-gray-600 dark:text-gray-300">Cargando...</p>
        </div>
    </div>

    <div class="flex flex-1 min-h-0 bg-gray-200 dark:bg-gray-900">
        <div class="flex flex-col flex-1 min-h-0 w-full">
            @include('navigations.header')
            <main class="flex-1 min-h-0 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        // Hide loading screen when the page has fully loaded
        window.addEventListener('load', function () {
            var el = document.getElementById('app-loader');
            if (!el) return;
            el.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(function () {
                el.style.display = 'none';
            }, 300);
        });
        // Failsafe: auto-hide after 7s in case 'load' doesn't fire
        setTimeout(function () {
            var el = document.getElementById('app-loader');
            if (el && getComputedStyle(el).display !== 'none') {
                el.style.display = 'none';
            }
        }, 7000);
    </script>
</body>

</html>