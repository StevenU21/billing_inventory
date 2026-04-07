<section class="flex-1 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-gray-100">Resumen de Facturacion</h4>

    <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
            <span>Subtotal</span>
            <span class="tabular-nums" x-text="formatMoney(calculateSubtotal())"></span>
        </div>
        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
            <span>Descuento</span>
            <span class="tabular-nums" x-text="formatMoney(calculateTotalDiscount())"></span>
        </div>
        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
            <span>Impuesto</span>
            <span class="tabular-nums" x-text="formatMoney(calculateTotalTax())"></span>
        </div>
        <div class="border-t border-gray-200 pt-2 text-lg font-extrabold text-gray-900 dark:border-gray-700 dark:text-gray-100">
            <div class="flex items-center justify-between">
                <span>Total</span>
                <span class="tabular-nums" x-text="formatMoney(calculateGrandTotal())"></span>
            </div>
        </div>
    </div>
</section>
