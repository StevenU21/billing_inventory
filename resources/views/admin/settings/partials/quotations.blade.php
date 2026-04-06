<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow" x-data="{
                            validityDays: {{ $quotationValidityDays ?? 7 }},
                            updating: false,
                            updateSettings() {
                                if (this.updating) return;
                                this.updating = true;

                                fetch('{{ route('settings.updateQuotationSettings') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        quotation_validity_days: this.validityDays
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                       // Success feedback (optional, currently silent update like input change)
                                       // alert('Configuración guardada');
// Notification removed as per request
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
            class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <i class="fas fa-file-invoice text-blue-600 dark:text-blue-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Configuración de Cotizaciones</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Personaliza el comportamiento de las proformas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="quotation_validity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Días de validez por defecto
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">
                Número de días que una proforma es válida antes de expirar.
            </p>
            <input type="number" id="quotation_validity_days" x-model="validityDays" @change="updateSettings()" min="1"
                max="365"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
        </div>
    </div>
</div>