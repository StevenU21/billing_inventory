<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <i class="fas fa-server text-blue-600 dark:text-blue-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Información del Sistema</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Datos técnicos del servidor</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">PHP</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ phpversion() }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Laravel</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ app()->version() }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Entorno</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ app()->environment() }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Base de datos</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">
                {{ config('database.default') }}
            </p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Sistema Operativo</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1">{{ php_uname('s') }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Servidor</p>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mt-1 truncate"
                title="{{ request()->server('SERVER_SOFTWARE') ?: 'N/A' }}">
                {{ Str::limit(request()->server('SERVER_SOFTWARE') ?: 'N/A', 30) }}
            </p>
        </div>
    </div>
</div>