<section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
        <i class="fas fa-receipt text-purple-500"></i>
        Datos de Facturacion
    </h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-1 md:col-span-2" @click.outside="openClientResults = false">
            <label for="client_search" class="text-sm font-medium text-gray-700 dark:text-gray-300">Cliente <span class="text-red-500">*</span></label>
            <div class="relative">
                <input
                    id="client_search"
                    type="text"
                    x-model="clientSearch"
                    @focus="openClientResults = true"
                    @input="openClientResults = true"
                    @keydown.escape="openClientResults = false"
                    placeholder="Buscar por nombre o documento..."
                    class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                />
                <div
                    x-show="openClientResults"
                    x-transition
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                >
                    <template x-if="filteredClients.length === 0">
                        <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Sin resultados</div>
                    </template>

                    <template x-for="client in filteredClients" :key="client.id">
                        <button
                            type="button"
                            @click="pickClient(client)"
                            class="flex w-full items-start justify-between gap-2 px-3 py-2 text-left text-sm hover:bg-purple-50 dark:hover:bg-gray-700"
                        >
                            <span class="font-medium text-gray-700 dark:text-gray-200" x-text="client.name || 'Cliente sin nombre'"></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="client.document"></span>
                        </button>
                    </template>
                </div>
            </div>
            <input type="hidden" name="client_id" :value="selectedClientId" required>
        </div>

        <div class="space-y-1">
            <label for="sale_date" class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
            <input
                id="sale_date"
                name="sale_date"
                type="date"
                max="{{ now()->toDateString() }}"
                value="{{ old('sale_date', now()->toDateString()) }}"
                class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            >
        </div>

        <div class="space-y-1">
            <label for="currency" class="text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
            <select
                id="currency"
                name="currency"
                x-model="selectedCurrency"
                class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            >
                @foreach($currencies as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <label for="payment_method_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">Metodo de pago</label>
            <select
                id="payment_method_id"
                name="payment_method_id"
                x-model="selectedPaymentMethod"
                :disabled="isCredit"
                :required="!isCredit"
                class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:disabled:bg-gray-900"
            >
                <option value="">Seleccionar...</option>
                @foreach($methods as $method)
                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Condicion</label>
            <label class="flex h-[40px] cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-3 dark:border-gray-600">
                <input type="checkbox" x-model="isCredit" class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                <span class="text-sm text-gray-700 dark:text-gray-200">Venta a credito</span>
            </label>
            <input type="hidden" name="is_credit" :value="isCredit ? 1 : 0">
        </div>
    </div>
</section>
