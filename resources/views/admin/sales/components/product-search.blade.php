<section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800" @click.outside="openProductResults = false">
    <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
        <i class="fas fa-search text-purple-500"></i>
        Buscar Producto
    </h3>
    <div class="relative">
        <input
            id="product_search"
            type="text"
            x-model="productSearch"
            @focus="openProductResults = true"
            @input="openProductResults = true"
            @keydown.escape="openProductResults = false"
            placeholder="Codigo, SKU o nombre del producto..."
            class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
        >
        <div
            x-show="openProductResults"
            x-transition
            class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
        >
            <template x-if="filteredVariants.length === 0">
                <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Sin productos</div>
            </template>

            <template x-for="variant in filteredVariants" :key="variant.id">
                <button
                    type="button"
                    @click="addItemFromVariant(variant)"
                    class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left hover:bg-purple-50 dark:hover:bg-gray-700"
                >
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="variant.label"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'SKU: ' + variant.sku"></p>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="formatMoney(currentUnitPrice(variant))"></span>
                </button>
            </template>
        </div>
    </div>
</section>
