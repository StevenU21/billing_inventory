@php
    $isEdit = isset($purchase);
    $currencies = ['NIO' => 'NIO - Córdobas', 'USD' => 'USD - Dólares'];

    // Preparar detalles para Alpine.js
    $initialDetails = [];
    if ($isEdit && $purchase->details) {
        foreach ($purchase->details as $detail) {
            $initialDetails[] = [
                'product_variant_id' => $detail->product_variant_id,
                'quantity' => (float) $detail->quantity,
                'unit_price' => $detail->unit_price?->getAmount()->toFloat() ?? 0,
                'tax_percentage' => (float) $detail->tax_percentage,
            ];
        }
    }
@endphp

<div x-data="purchaseForm()" class="space-y-6">
    {{-- Header Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-file-invoice text-purple-500"></i>
            Información General
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Reference --}}
            <x-inputs.text name="reference" label="Referencia / Factura" :value="old('reference', $isEdit ? $purchase->reference : '')" placeholder="FAC-001" required />

            {{-- Supplier --}}
            <x-inputs.select name="supplier_id" label="Proveedor" :options="$entities" :selected="old('supplier_id', $isEdit ? $purchase->supplier_id : '')" placeholder="Seleccionar proveedor..." required />

            {{-- Payment Method --}}
            <x-inputs.select name="payment_method_id" label="Método de Pago" :options="$methods"
                :selected="old('payment_method_id', $isEdit ? $purchase->payment_method_id : '')"
                placeholder="Seleccionar método..." required />

            {{-- Purchase Date --}}
            <x-inputs.date name="purchase_date" label="Fecha de Compra" :value="old('purchase_date', $isEdit ? $purchase->purchase_date?->format('Y-m-d') : now()->format('Y-m-d'))" :max="now()->format('Y-m-d')" />

            {{-- Currency --}}
            <x-inputs.select name="currency" label="Moneda" :options="$currencies" :selected="old('currency', $isEdit ? $purchase->currency : 'NIO')" />

            {{-- Is Credit --}}
            <div class="flex items-end pb-2">
                <x-inputs.checkbox name="is_credit" label="¿Es crédito?" :checked="old('is_credit', $isEdit ? $purchase->is_credit : false)" />
            </div>
        </div>

        @if($errors->any())
            <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/30 rounded-lg border border-red-300 dark:border-red-700">
                <p class="text-sm text-red-600 dark:text-red-400 font-medium">Por favor corrige los siguientes errores:</p>
                <ul class="mt-1 text-sm text-red-500 dark:text-red-400 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Details Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <i class="fas fa-boxes text-purple-500"></i>
                Detalles de Compra
            </h2>
            <button type="button" @click="addDetail()"
                class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                <i class="fas fa-plus"></i>
                Agregar Línea
            </button>
        </div>

        {{-- Details Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                            Producto</th>
                        <th
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">
                            Cantidad</th>
                        <th
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">
                            Precio Unit.</th>
                        <th
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">
                            IVA %</th>
                        <th
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">
                            Subtotal</th>
                        <th class="px-3 py-2 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(detail, index) in details" :key="index">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-3 py-2">
                                <select :name="'details[' + index + '][product_variant_id]'"
                                    x-model="detail.product_variant_id" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]">
                                    <option value="">Seleccionar producto...</option>
                                    @foreach($productVariants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->product->name }}
                                            {{ $variant->option1 ? "- {$variant->option1}" : '' }}
                                            {{ $variant->option2 ? "/ {$variant->option2}" : '' }} (SKU:
                                            {{ $variant->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'details[' + index + '][quantity]'"
                                    x-model.number="detail.quantity" step="0.0001" min="0.0001" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px] text-right" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'details[' + index + '][unit_price]'"
                                    x-model.number="detail.unit_price" step="0.01" min="0.01" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px] text-right" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'details[' + index + '][tax_percentage]'"
                                    x-model.number="detail.tax_percentage" step="0.01" min="0" max="100" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px] text-right" />
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300 font-medium">
                                <span x-text="formatMoney(calculateLineTotal(detail))"></span>
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" @click="removeDetail(index)"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                    title="Eliminar línea">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="details.length === 0">
                        <td colspan="6" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No hay líneas de detalle. Haz clic en "Agregar Línea" para comenzar.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="mt-4 flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span x-text="formatMoney(calculateSubtotal())">C$0.00</span>
                </div>
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>IVA:</span>
                    <span x-text="formatMoney(calculateTax())">C$0.00</span>
                </div>
                <div
                    class="flex justify-between text-lg font-bold text-gray-800 dark:text-gray-200 border-t border-gray-300 dark:border-gray-600 pt-2">
                    <span>Total:</span>
                    <span x-text="formatMoney(calculateTotal())">C$0.00</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('purchases.index') }}"
            class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
            Cancelar
        </a>
        <button type="submit"
            class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-save"></i>
            {{ $isEdit ? 'Actualizar Compra' : 'Guardar Compra' }}
        </button>
    </div>
</div>

@push('scripts')
    <script>
        function purchaseForm() {
            return {
                details: @json($initialDetails),

                init() {
                    console.log('Purchase form initialized', this.details);
                },

                addDetail() {
                    this.details.push({
                        product_variant_id: '',
                        quantity: 1,
                        unit_price: 0,
                        tax_percentage: 15,
                    });
                },

                removeDetail(index) {
                    this.details.splice(index, 1);
                },

                calculateLineTotal(detail) {
                    const subtotal = (detail.quantity || 0) * (detail.unit_price || 0);
                    const tax = subtotal * ((detail.tax_percentage || 0) / 100);
                    return subtotal + tax;
                },

                calculateSubtotal() {
                    return this.details.reduce((sum, d) => sum + ((d.quantity || 0) * (d.unit_price || 0)), 0);
                },

                calculateTax() {
                    return this.details.reduce((sum, d) => {
                        const subtotal = (d.quantity || 0) * (d.unit_price || 0);
                        return sum + (subtotal * ((d.tax_percentage || 0) / 100));
                    }, 0);
                },

                calculateTotal() {
                    return this.calculateSubtotal() + this.calculateTax();
                },

                formatMoney(value) {
                    const formatted = new Intl.NumberFormat('es-NI', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(value || 0);
                    return 'C$' + formatted;
                }
            };
        }
    </script>
@endpush