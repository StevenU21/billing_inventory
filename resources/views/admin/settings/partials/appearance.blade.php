<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
            <i class="fas fa-paint-brush text-yellow-600 dark:text-yellow-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Apariencia</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Personaliza el tema de la aplicación</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Light Mode -->
        <button @click="setTheme('light')"
            class="relative flex flex-col items-center p-4 cursor-pointer rounded-lg border-2 transition-all duration-200 focus:outline-none"
            :class="theme === 'light' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div class="w-full aspect-video rounded bg-gray-100 mb-3 border border-gray-200 overflow-hidden relative">
                <!-- Mock UI Light -->
                <div class="absolute top-0 left-0 w-1/4 h-full bg-white border-r border-gray-200"></div>
                <div class="absolute top-0 right-0 w-3/4 h-full bg-gray-50"></div>
            </div>
            <div class="flex items-center gap-2">
                <i class="fas fa-sun text-yellow-500"></i>
                <span class="font-medium text-gray-900 dark:text-gray-100">Claro</span>
            </div>
            <div x-show="theme === 'light'" class="absolute top-2 right-2 text-purple-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>

        <!-- Dark Mode -->
        <button @click="setTheme('dark')"
            class="relative flex flex-col items-center p-4 cursor-pointer rounded-lg border-2 transition-all duration-200 focus:outline-none"
            :class="theme === 'dark' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div class="w-full aspect-video rounded bg-gray-900 mb-3 border border-gray-700 overflow-hidden relative">
                <!-- Mock UI Dark -->
                <div class="absolute top-0 left-0 w-1/4 h-full bg-gray-800 border-r border-gray-700"></div>
                <div class="absolute top-0 right-0 w-3/4 h-full bg-gray-900"></div>
            </div>
            <div class="flex items-center gap-2">
                <i class="fas fa-moon text-indigo-400"></i>
                <span class="font-medium text-gray-900 dark:text-gray-100">Oscuro</span>
            </div>
            <div x-show="theme === 'dark'" class="absolute top-2 right-2 text-purple-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>

        <!-- System Mode -->
        <button @click="setTheme('system')"
            class="relative flex flex-col items-center p-4 cursor-pointer rounded-lg border-2 transition-all duration-200 focus:outline-none"
            :class="theme === 'system' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
            <div
                class="w-full aspect-video rounded bg-gradient-to-br from-gray-100 to-gray-900 mb-3 border border-gray-300 dark:border-gray-600 overflow-hidden relative">
                <!-- Mock UI System -->
                <div class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400 font-xs">
                    Auto
                </div>
            </div>
            <div class="flex items-center gap-2">
                <i class="fas fa-desktop text-gray-500"></i>
                <span class="font-medium text-gray-900 dark:text-gray-100">Sistema</span>
            </div>
            <div x-show="theme === 'system'" class="absolute top-2 right-2 text-purple-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </button>
    </div>
</div>