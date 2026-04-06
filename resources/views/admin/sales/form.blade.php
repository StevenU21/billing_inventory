@php
    $isEdit = isset($sale);
    $currencies = ['NIO' => 'NIO - Córdobas', 'USD' => 'USD - Dólares'];

    // Preparar items para Alpine.js
    $initialItems = [];
    if ($isEdit && $sale->saleDetails) {
        foreach ($sale->saleDetails as $detail) {
            $initialItems[] = [
                'product_variant_id' => $detail->product_variant_id,
                'quantity' => (float) $detail->quantity,
                'unit_price' => $detail->unit_price?->getAmount()->toFloat() ?? 0,
                'discount_percentage' => (float) $detail->discount_percentage,
                'tax_percentage' => (float) $detail->tax_percentage,
            ];
        }
    }
@endphp

<div x-data="saleForm()" class="space-y-6">
    {{-- Header Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-file-invoice text-purple-500"></i>
            Información General
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Client --}}
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente <span
                        class="text-red-500">*</span></label>
                <select name="client_id"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]"
                    required>
                    <option value="">Seleccionar cliente...</option>
                    @foreach($clientEntities as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $isEdit ? $sale->client_id : '') == $client->id ? 'selected' : '' }}>
                            {{ $client->first_name }} {{ $client->last_name }}
                            {{ $client->social_reason ? "({$client->social_reason})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Method --}}
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Método de Pago <span
                        class="text-red-500">*</span></label>
                <select name="payment_method_id"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]"
                    required>
                    <option value="">Seleccionar método...</option>
                    @foreach($methods as $method)
                        <option value="{{ $method->id }}" {{ old('payment_method_id', $isEdit ? $sale->payment_method_id : '') == $method->id ? 'selected' : '' }}>
                            {{ $method->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sale Date --}}
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Venta</label>
                <input type="date" name="sale_date"
                    value="{{ old('sale_date', $isEdit ? $sale->sale_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    max="{{ now()->format('Y-m-d') }}"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]" />
            </div>

            {{-- Currency --}}
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
                <select name="currency"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]">
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}" {{ old('currency', $isEdit ? $sale->currency : 'NIO') == $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Is Credit --}}
            <div class="flex items-end pb-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_credit" name="is_credit" value="1" {{ old('is_credit', $isEdit ? $sale->is_credit : false) ? 'checked' : '' }}
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="is_credit" class="text-sm font-medium text-gray-700 dark:text-gray-300">¿Es
                        crédito?</label>
                </div>
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

    {{-- Items Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <i class="fas fa-boxes text-purple-500"></i>
                Ítems de Venta
            </h2>
            <button type="button" @click="addItem()"
                class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                <i class="fas fa-plus"></i>
                Agregar Ítem
            </button>
        </div>

        {{-- Items Table --}}
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
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">
                            Desc. %</th>
                        <th
                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">
                            Subtotal</th>
                        <th class="px-3 py-2 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-3 py-2">
                                <select :name="'items[' + index + '][product_variant_id]'"
                                    x-model="item.product_variant_id" @change="updateItemPrice(index)" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px]">
                                    <option value="">Seleccionar producto...</option>
                                    @foreach($productVariants as $variant)
                                        <option value="{{ $variant->id }}"
                                            data-price="{{ $variant->price?->getAmount()->toFloat() ?? 0 }}"
                                            data-tax="{{ $variant->product->tax?->percentage ?? 0 }}">
                                            {{ $variant->product->name }}
                                            @if($variant->attributeValues->isNotEmpty())
                                                ({{ $variant->attributeValues->pluck('value')->join(' / ') }})
                                            @endif
                                            - SKU: {{ $variant->sku }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" :name="'items[' + index + '][unit_price]'"
                                    x-model="item.unit_price" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + index + '][quantity]'"
                                    x-model.number="item.quantity" step="0.0001" min="0.0001" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px] text-right" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + index + '][discount_percentage]'"
                                    x-model.number="item.discount_percentage" step="0.01" min="0" max="100" required
                                    class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 h-[38px] text-right" />
                            </td>
                            <input type="hidden" :name="'items[' + index + '][tax_percentage]'"
                                x-model="item.tax_percentage" />
                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300 font-medium">
                                <span x-text="formatMoney(calculateLineTotal(item))"></span>
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" @click="removeItem(index)"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                    title="Eliminar ítem">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="items.length === 0">
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No hay ítems de venta. Haz clic en "Agregar Ítem" para comenzar.</p>
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
                    <span>Descuento:</span>
                    <span x-text="formatMoney(calculateTotalDiscount())">C$0.00</span>
                </div>
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>IVA:</span>
                    <span x-text="formatMoney(calculateTotalTax())">C$0.00</span>
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
        <a href="{{ route('admin.sales.index') }}"
            class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
            Cancelar
        </a>
        <button type="submit"
            class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-save"></i>
            {{ $isEdit ? 'Actualizar Venta' : 'Guardar Venta' }}
        </button>
    </div>
</div>

@push('scripts')
    <script>
        function saleForm() {
            return {
                items: @json($initialItems),

                init() {
                    console.log('Sale form initialized', this.items);
                },

                addItem() {
                    this.items.push({
                        product_variant_id: '',
                        quantity: 1,
                        unit_price: 0,
                        discount_percentage: 0,
                        tax_percentage: 15,
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                updateItemPrice(index) {
                    const selectElement = document.querySelector(`select[name="items[${index}][product_variant_id]"]`);
                    const selectedOption = selectElement?.options[selectElement.selectedIndex];

                    if (selectedOption && selectedOption.value) {
                        const price = parseFloat(selectedOption.dataset.price) || 0;
                        const tax = parseFloat(selectedOption.dataset.tax) || 0;

                        this.items[index].unit_price = price;
                        this.items[index].tax_percentage = tax;
                    }
                },

                calculateLineTotal(item) {
                    const gross = (item.quantity || 0) * (item.unit_price || 0);
                    const discount = gross * ((item.discount_percentage || 0) / 100);
                    const subtotal = gross - discount;
                    const tax = subtotal * ((item.tax_percentage || 0) / 100);
                    return subtotal + tax;
                },

                calculateSubtotal() {
                    return this.items.reduce((sum, d) => sum + ((d.quantity || 0) * (d.unit_price || 0)), 0);
                },

                calculateTotalDiscount() {
                    return this.items.reduce((sum, d) => {
                        const gross = (d.quantity || 0) * (d.unit_price || 0);
                        return sum + (gross * ((d.discount_percentage || 0) / 100));
                    }, 0);
                },

                calculateTotalTax() {
                    return this.items.reduce((sum, d) => {
                        const gross = (d.quantity || 0) * (d.unit_price || 0);
                        const discount = gross * ((d.discount_percentage || 0) / 100);
                        const subtotal = gross - discount;
                        return sum + (subtotal * ((d.tax_percentage || 0) / 100));
                    }, 0);
                },

                calculateTotal() {
                    const subtotal = this.calculateSubtotal();
                    const discount = this.calculateTotalDiscount();
                    const tax = this.calculateTotalTax();
                    return (subtotal - discount) + tax;
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