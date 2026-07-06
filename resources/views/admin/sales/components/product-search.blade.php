<section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between gap-3">
        <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
            <i class="fas fa-box-open text-purple-500"></i>
            Productos
        </h3>
        <button
            type="button"
            @click="openProductModal()"
            class="inline-flex h-[40px] items-center gap-2 rounded-lg bg-purple-600 px-4 text-sm font-semibold text-white transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-400"
        >
            <i class="fas fa-search"></i>
            Buscar producto
        </button>
    </div>
</section>

<x-modal
    title="Buscar producto"
    description="Usa el buscador y filtros para cargar solo los productos necesarios."
    maxWidth="3xl"
    showVar="isProductModalOpen"
    onClose="closeProductModal()"
>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="md:col-span-1">
                <label for="modal_product_search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Buscar</label>
                <input
                    id="modal_product_search"
                    type="text"
                    x-model="productSearchTerm"
                    @input="onProductSearchInput()"
                    placeholder="Nombre, codigo, SKU o barcode"
                    class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
            </div>

            <div>
                <label for="modal_category_filter" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Categoria</label>
                <select
                    id="modal_category_filter"
                    x-model="productFilters.category_id"
                    @change="searchProducts(1)"
                    class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    <option value="">Todas</option>
                    <template x-for="option in categoryOptions" :key="option.value">
                        <option :value="option.value" x-text="option.label"></option>
                    </template>
                </select>
            </div>

            <div>
                <label for="modal_brand_filter" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Marca</label>
                <select
                    id="modal_brand_filter"
                    x-model="productFilters.brand_id"
                    @change="searchProducts(1)"
                    class="h-[40px] w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    <option value="">Todas</option>
                    <template x-for="option in brandOptions" :key="option.value">
                        <option :value="option.value" x-text="option.label"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
            <div x-show="isLoadingProducts" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-spinner fa-spin"></i>
                Cargando productos...
            </div>

            <div x-show="!isLoadingProducts && paginatedProducts.length === 0" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                Sin resultados para esta busqueda.
            </div>

            <div x-show="!isLoadingProducts && paginatedProducts.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="variant in paginatedProducts" :key="variant.id">
                    <article class="flex flex-col gap-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="variant.label"></h4>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400" x-text="'SKU: ' + (variant.sku || '-')"></p>
                            </div>
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300" x-text="'Stock: ' + Number(variant.stock || 0)"></span>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <p x-text="'Categoria: ' + (variant.category_name || '-')"></p>
                            <p x-text="'Marca: ' + (variant.brand_name || '-')"></p>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-1">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="formatMoney(currentUnitPrice(variant))"></p>
                            <button
                                type="button"
                                @click="addItemFromVariant(variant)"
                                class="inline-flex items-center gap-1 rounded-md bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-purple-700"
                            >
                                <i class="fas fa-plus"></i>
                                Agregar
                            </button>
                        </div>
                    </article>
                </template>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Mostrando
                <strong x-text="paginatedProducts.length"></strong>
                de
                <strong x-text="productMeta.total"></strong>
                productos
            </p>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="searchProducts(productMeta.current_page - 1)"
                    :disabled="productMeta.current_page <= 1 || isLoadingProducts"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300"
                >
                    Anterior
                </button>
                <span class="text-xs text-gray-600 dark:text-gray-300" x-text="'Pagina ' + productMeta.current_page + ' / ' + productMeta.last_page"></span>
                <button
                    type="button"
                    @click="searchProducts(productMeta.current_page + 1)"
                    :disabled="productMeta.current_page >= productMeta.last_page || isLoadingProducts"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</x-modal>
