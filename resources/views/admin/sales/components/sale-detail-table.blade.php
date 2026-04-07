<section class="flex-1 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="h-full flex flex-col">
        <div class="h-full overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/40">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-200">Producto</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-200">Precio</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-200">Cant.</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-200">Desc. %</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-200">Impuesto</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600 dark:text-gray-200">Total linea</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <template x-for="(item, index) in items" :key="item.key">
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-medium text-gray-700 dark:text-gray-200" x-text="item.label"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'SKU: ' + item.sku"></p>
                                <input type="hidden" :name="'items[' + index + '][product_variant_id]'" :value="item.product_variant_id">
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                <span x-text="formatMoney(item.unit_price)"></span>
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    step="0.0001"
                                    min="0.0001"
                                    :name="'items[' + index + '][quantity]'"
                                    x-model.number="item.quantity"
                                    class="h-[36px] w-24 rounded-lg border border-gray-300 bg-white px-2 text-right text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                >
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" x-model="item.discount" class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        :name="'items[' + index + '][discount_percentage]'"
                                        x-model.number="item.discount_percentage"
                                        :disabled="!item.discount"
                                        :required="item.discount"
                                        class="h-[36px] w-24 rounded-lg border border-gray-300 bg-white px-2 text-right text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:disabled:bg-gray-900"
                                    >
                                    <input type="hidden" :name="'items[' + index + '][discount]'" :value="item.discount ? 1 : 0">
                                </div>
                            </td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                <span x-text="item.tax_percentage + '%' "></span>
                            </td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums text-gray-800 dark:text-gray-100">
                                <span x-text="formatMoney(calculateLineTotal(item))"></span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" @click="removeItem(index)"
                                    class="rounded-lg p-1.5 text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                    title="Quitar producto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="items.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-cart-plus mb-2 text-2xl"></i>
                            <p>Los productos agregados apareceran aqui.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
