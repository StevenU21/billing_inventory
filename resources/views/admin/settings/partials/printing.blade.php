<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow" x-data="{
                    printerDefault: '{{ $defaultPrinter }}',
                    printerPaperSize: '{{ $paperSize }}',
                    updating: false,
                    updatePrinterSettings() {
                        this.updating = true;
                        fetch('{{ route('settings.updatePrinterSettings') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                            },
                            body: JSON.stringify({
                                printer_default: this.printerDefault,
                                printer_paper_size: this.printerPaperSize
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Optional: Show success toast
                            } else {
                                alert('Error al actualizar la configuración de impresión');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al actualizar la configuración de impresión');
                        })
                        .finally(() => {
                            this.updating = false;
                        });
                    }
                }">
    <div class="flex items-center gap-3 mb-4">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
            <i class="fas fa-print text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Impresión</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configuración de impresoras y tickets</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Default Printer Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Impresora
                Predeterminada</label>
            <select x-model="printerDefault" @change="updatePrinterSettings()"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Seleccionar impresora...</option>
                @foreach ($printers as $printer)
                    <option value="{{ $printer->name }}">{{ $printer->displayName }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                La impresora que se usará para tickets y reportes rápidos.
            </p>
        </div>

        <!-- Paper Size Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Formato de Papel</label>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="printerPaperSize = 'A4'; updatePrinterSettings()"
                    :class="printerPaperSize === 'A4' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="flex flex-col items-center justify-center p-3 rounded-lg border transition-all">
                    <i class="fas fa-file-alt text-2xl mb-2"
                        :class="printerPaperSize === 'A4' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"></i>
                    <span class="text-sm font-medium"
                        :class="printerPaperSize === 'A4' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">A4
                        / Carta</span>
                </button>

                <button type="button" @click="printerPaperSize = '80mm'; updatePrinterSettings()"
                    :class="printerPaperSize === '80mm' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                    class="flex flex-col items-center justify-center p-3 rounded-lg border transition-all">
                    <i class="fas fa-receipt text-2xl mb-2"
                        :class="printerPaperSize === '80mm' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"></i>
                    <span class="text-sm font-medium"
                        :class="printerPaperSize === '80mm' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">Ticket
                        80mm</span>
                </button>
            </div>
        </div>
    </div>
</div>