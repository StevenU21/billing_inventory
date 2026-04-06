<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

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

<body>
    <div class="flex items-center min-h-screen p-6 bg-gray-200 dark:bg-gray-900">
        <div class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800">
            @yield('content')
        </div>
    </div>
</body>

</html>