<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow" x-data='{
                    backupFrequency: @json($backupFrequency),
                    backupRetention: @json($backupRetention),
                    currentPath: @json($backupPath ?? "Predeterminado (App Data)"),
                    selecting: false,
                    updating: false,
                    
                    updateBackupSettings() {
                        if (this.updating) return;
                        this.updating = true;
                        
                        fetch("{{ route('settings.updateBackupSettings') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
                            },
                            body: JSON.stringify({
                                backup_frequency: this.backupFrequency,
                                backup_retention: this.backupRetention
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== "success") {
                                alert("Error al actualizar la configuración de backups");
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("Error al actualizar la configuración de backups");
                        })
                        .finally(() => {
                            this.updating = false;
                        });
                    },

                    selectPath() {
                        if (this.selecting) return;
                        this.selecting = true;

                        fetch("{{ route('settings.selectBackupPath') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success" && data.path) {
                                this.currentPath = data.path;
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                        })
                        .finally(() => {
                            this.selecting = false;
                        });
                    }
                }'>
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
            <i class="fas fa-hdd text-cyan-600 dark:text-cyan-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Copias de Seguridad</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Programa la frecuencia y retención de tus respaldos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Frequency -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Frecuencia Automática</label>
            <select x-model="backupFrequency" @change="updateBackupSettings()"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-cyan-500 focus:ring-cyan-500">
                <option value="1minute">Cada minuto (Recomendado)</option>
                <option value="2hours">Cada 2 horas (Recomendado)</option>
                <option value="daily">Diario</option>
                <option value="weekly">Semanal</option>
                <option value="on_close">Al cerrar la aplicación</option>
                <option value="manual">Solo manual (Desactivado)</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Define cada cuánto tiempo se crea una copia automática.
            </p>
        </div>

        <!-- Retention -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Retención de Archivos</label>
            <select x-model="backupRetention" @change="updateBackupSettings()"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-cyan-500 focus:ring-cyan-500">
                <option value="2">Últimos 2 respaldos</option>
                <option value="5">Últimos 5 respaldos</option>
                <option value="10">Últimos 10 respaldos</option>
                <option value="20">Últimos 20 respaldos</option>
                <option value="30">Últimos 30 respaldos</option>
                <option value="40">Últimos 40 respaldos</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Número máximo de copias a guardar antes de borrar las antiguas.
            </p>
        </div>
    </div>

    <!-- Backup Path Selection -->
    <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Ubicación de Respaldos</label>
        <div class="flex items-center gap-3">
            <div class="flex-1 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm font-mono text-gray-600 dark:text-gray-300 truncate border border-gray-200 dark:border-gray-700"
                x-text="currentPath"></div>
            <button type="button" @click="selectPath()" :disabled="selecting"
                class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 disabled:opacity-50 transition-colors">
                <i class="fas fa-folder-open mr-2"></i>
                <span x-text="selecting ? 'Seleccionando...' : 'Cambiar Carpeta'"></span>
            </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Selecciona una carpeta segura (ej. Google Drive, Dropbox) para tus copias de seguridad.
        </p>
    </div>
</div>