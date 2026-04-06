<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow" x-data="{
                    global: {{ $notificationsGlobal ? 'true' : 'false' }},
                    inventory: {{ $notificationsInventory ? 'true' : 'false' }},
                    system: {{ $notificationsSystem ? 'true' : 'false' }},
                    updating: false,
                    updateSetting(key, value) {
                        // Optimistic update
                        this[key] = value;

                        // If global is turned off, visual feedback for children could be handled here if desired

                        fetch('{{ route('settings.updateNotificationSettings') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                            },
                            body: JSON.stringify({ key: 'notifications_' + key, value: value })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== 'success') {
                                // Revert on failure
                                this[key] = !value;
                                alert('Error al actualizar la configuración');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this[key] = !value;
                            alert('Error al actualizar la configuración');
                        });
                    }
                }">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <i class="fas fa-bell text-red-600 dark:text-red-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Notificaciones</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona las alertas del sistema</p>
        </div>
    </div>

    <div class="space-y-4">
        <!-- Global Toggle -->
        <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Notificaciones Globales
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Activar o desactivar todas las notificaciones
                </p>
            </div>
            <div class="ml-4">
                <button type="button" @click="updateSetting('global', !global)"
                    :class="global ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <span :class="global ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                    </span>
                </button>
            </div>
        </div>

        <!-- Categories -->
        <div class="pl-4 space-y-4 border-l-2 border-gray-100 dark:border-gray-700" x-show="global" x-transition>
            <!-- Inventory Category -->
            <div class="flex items-center justify-between p-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        <i class="fas fa-box text-gray-400 mr-2"></i>Inventario
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Alertas de bajo stock</p>
                </div>
                <div class="ml-4">
                    <button type="button" @click="updateSetting('inventory', !inventory)"
                        :class="inventory ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700'"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <span :class="inventory ? 'translate-x-4' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                        </span>
                    </button>
                </div>
            </div>

            <!-- System Category -->
            <div class="flex items-center justify-between p-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        <i class="fas fa-server text-gray-400 mr-2"></i>Sistema
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Backups y actualizaciones</p>
                </div>
                <div class="ml-4">
                    <button type="button" @click="updateSetting('system', !system)"
                        :class="system ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700'"
                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <span :class="system ? 'translate-x-4' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>