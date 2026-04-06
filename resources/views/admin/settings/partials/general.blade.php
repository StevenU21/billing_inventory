<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
            <i class="fas fa-cog text-purple-600 dark:text-purple-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Información General</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Datos básicos de la aplicación</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nombre de la aplicación
            </p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ $appName }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Versión</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ $appVersion }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Zona horaria</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ $appTimezone }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Idioma</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ $appLocale }}</p>
        </div>
    </div>
</div>