<section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
            <i class="fas fa-shopping-cart text-purple-500"></i>
            Detalle de Venta
        </h3>
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <span class="rounded-lg bg-gray-100 px-3 py-1 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                Productos: <strong x-text="items.length"></strong>
            </span>
            <span class="rounded-lg bg-purple-50 px-3 py-1 text-purple-700 dark:bg-purple-900/20 dark:text-purple-200">
                Moneda: <strong x-text="selectedCurrency"></strong>
            </span>
        </div>
    </div>
</section>
