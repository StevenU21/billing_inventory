@extends('layouts.app')
@section('title', 'Inventarios')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumb :items="[
            ['label' => 'Modulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Inventarios'],
        ]" />

        <!-- Page header card -->
        <x-page-header title="Inventarios" subtitle="Administra existencias por variante de producto." icon="fa-boxes"
            :action-href="route('inventories.create')" action-label="Nuevo Inventario"
            action-permission="create inventories">
        </x-page-header>

        <!-- Mensajes de éxito -->
        <div class="mt-4">
            <x-session-message />
        </div>

        <!-- Filtros -->
        <x-filter-card action="{{ route('inventories.index') }}">

            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Buscar</label>
                <x-autocomplete id="inventory_search" name="filter[search]"
                    url="{{ route('product_variants.autocomplete') }}" :value="request('filter.search')"
                    placeholder="Producto, SKU o código de barras" min="2" debounce="250" :dedupe-text="true" />
            </div>

            <x-filter-card.select name="filter[category_id]" label="Categoría" :options="$categories"
                :selected="request('filter.category_id')" placeholder="Todas" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[brand_id]" label="Marca" :options="$brands"
                :selected="request('filter.brand_id')" id="brand_id" placeholder="Todas" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[stock_level]" label="Stock" :options="$stockLevels"
                :selected="request('filter.stock_level')" placeholder="Todos" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[tax_id]" label="Impuesto" :options="$taxes"
                :selected="request('filter.tax_id')" placeholder="Todos" class="col-span-6 lg:col-span-2" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <x-table :resource="$inventories">
            <x-slot name="thead">
                <x-table.th>Código</x-table.th>
                <x-table.th>Producto / Variante</x-table.th>
                <x-table.th>Stock</x-table.th>
                <x-table.th>Mínimo</x-table.th>
                <x-table.th>Compra</x-table.th>
                <x-table.th>Venta</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($inventories as $inventory)
                    <x-table.tr>
                        <x-table.td class="whitespace-nowrap">
                            <div class="flex flex-col">
                                {{-- SKU (Primary - changes per variant) --}}
                                <span class="font-mono text-sm text-gray-300">
                                    {{ $inventory->productVariant->sku ?? '-' }}
                                </span>
                                {{-- Product Code (Secondary - repeats) --}}
                                <span class="font-mono text-[10px] text-gray-500">
                                    {{ $inventory->productVariant->product->code ?? '' }}
                                </span>
                            </div>
                        </x-table.td>

                        <x-table.td>
                            @if ($inventory->productVariant)
                                <div class="flex items-center gap-3">
                                    @if($inventory->productVariant->has_real_image)
                                        <img src="{{ $inventory->productVariant->image_url }}" alt="{{ $inventory->productVariant->product->name }}"
                                            class="w-10 h-10 rounded object-cover border border-gray-700 flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($inventory->productVariant->product->name ?? 'P', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="flex flex-col">
                                        {{-- 1. Variant Options (PRIMARY - The Differentiator) --}}
                                        <span class="text-sm font-semibold text-white">
                                            {{ $inventory->productVariant->attributeValues->pluck('value')->join(' / ') ?: 'Estándar' }}
                                        </span>
                                        
                                        {{-- 2. Product Name (SECONDARY - The Context) --}}
                                        <span class="text-xs font-normal text-gray-500">
                                            {{ $inventory->productVariant->product->name ?? '-' }}
                                        </span>

                                    </div>
                                </div>
                            @else
                                -
                            @endif
                        </x-table.td>

                        <x-table.td>
                            {{ (float) $inventory->stock }} 
                            <span class="text-xs text-gray-500">{{ $inventory->productVariant->product->unitMeasure->symbol ?? '' }}</span>
                        </x-table.td>
                        <x-table.td>
                            {{ (float) $inventory->min_stock }}
                            <span class="text-xs text-gray-500">{{ $inventory->productVariant->product->unitMeasure->symbol ?? '' }}</span>
                        </x-table.td>
                        <x-table.td>{{ $inventory->formatted_purchase_price }}</x-table.td>
                        <x-table.td>{{ $inventory->formatted_sale_price }}</x-table.td>

                        <x-table.dropdown-actions 
                            :delete-url="route('inventories.destroy', $inventory)"
                            delete-message="¿Seguro de eliminar este inventario?"
                            delete-title="Eliminar"
                            delete-icon="fa-trash">
                            
                            <x-table.dropdown-action-item 
                                :href="route('inventories.show', $inventory)" 
                                icon="fa-eye"
                                title="Ver detalles del inventario">
                                Ver Detalle
                            </x-table.dropdown-action-item>

                            <x-table.dropdown-action-item 
                                :href="route('inventories.edit', $inventory)" 
                                icon="fa-exchange-alt"
                                title="Realizar movimiento de inventario">
                                Movimiento
                            </x-table.dropdown-action-item>
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-boxes fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No hay inventarios registrados</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection