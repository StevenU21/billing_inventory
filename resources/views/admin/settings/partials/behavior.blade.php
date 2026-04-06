<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow" x-data="{
                            openAtLogin: {{ $openAtLogin ? 'true' : 'false' }},
                            updating: false,
                            toggleOpenAtLogin() {
                                if (this.updating) return;

                                this.updating = true;
                                const newValue = !this.openAtLogin;

                                fetch('{{ route('settings.updateOpenAtLogin') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                    },
                                    body: JSON.stringify({ open_at_login: newValue })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        this.openAtLogin = data.open_at_login;
                                    } else {
                                        alert('Error al actualizar la configuración');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error al actualizar la configuración');
                                })
                                .finally(() => {
                                    this.updating = false;
                                });
                            }
                        }">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <i class="fas fa-rocket text-green-600 dark:text-green-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Comportamiento</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configuración de inicio de la aplicación</p>
        </div>
    </div>

    <div class="space-y-4">
        <!-- Open at Login Toggle -->
        <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <div class="flex-1">
                <label for="open-at-login" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Abrir al iniciar sesión
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    La aplicación se abrirá automáticamente cuando inicies sesión en tu sistema
                </p>
            </div>
            <div class="ml-4">
                <button type="button" @click="toggleOpenAtLogin()" :disabled="updating"
                    :class="openAtLogin ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span :class="openAtLogin ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>