@extends('layouts.app')
@section('title', 'Productos')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Listado de Productos'],
        ]" />

        <x-page-header title="Productos" subtitle="Gestión detallada de catálogo y existencias." icon="fa-boxes"
            :action-href="route('products.create')" action-label="Nuevo Producto" action-permission="create products">
        </x-page-header>

        <x-filter-card :action="route('products.index')">

            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Buscar</label>
                <x-autocomplete name="filter[search]" :value="request('filter.search')"
                    url="{{ route('products.autocomplete') }}" placeholder="Nombre, código, SKU..." id="search" />
            </div>

            <x-filter-card.select name="filter[category_id]" label="Categoría" :options="$categories" :selected="request('filter.category_id')"
                placeholder="Todas" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[brand_id]" label="Marca" :options="$brands" :selected="request('filter.brand_id')"
                placeholder="Todas" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[status]" label="Estado" :options="[
                'draft' => 'Borrador',
                'available' => 'Disponible',
                'archived' => 'Archivado',
            ]" :selected="request('filter.status')"
                placeholder="Todos" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[unit_measure_id]" label="Unidad" :options="$units"
                :selected="request('filter.unit_measure_id')" placeholder="Todas"
                class="col-span-6 lg:col-span-2" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-table :resource="$products">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Imagen</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Código</x-table.th>
                <x-table.th>Marca</x-table.th>
                <x-table.th>Impuesto</x-table.th>
                <x-table.th>Variantes</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($products as $product)
                    <x-table.tr>

                        <x-table.td-folio :id="$product->id" />

                        <x-table.td>
                            @if($product->has_real_image)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                    class="w-10 h-10 rounded object-cover border border-gray-700">
                            @else
                                <div class="w-10 h-10 rounded bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white text-xs font-bold">
                                    {{ $product->initials }}
                                </div>
                            @endif
                        </x-table.td>

                        <x-table.td>
                            <span class="font-medium text-gray-200">{{ $product->name }}</span>
                        </x-table.td>

                        <x-table.td-text font="mono" variant="muted" size="sm">
                            {{ $product->code }}
                        </x-table.td-text>

                        <x-table.td-stacked
                            :top="$product->brand->name ?? '-'"
                            :middle="$product->brand->category->name ?? '-'"
                            top-class="text-gray-200"
                        />

                        <x-table.td-text font="mono" variant="muted" size="sm">
                            {{ $product->tax->name ?? '-' }}
                        </x-table.td-text>

                        <x-table.td>
                            @if($product->variant_badges['has_options'])
                                <div class="flex flex-col gap-1.5 max-w-[200px]">
                                    @foreach($product->variant_badges['attributes'] as $attrName => $data)
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] uppercase tracking-wider text-gray-500 font-bold">{{ $attrName }}</span>
                                            <div class="flex flex-wrap gap-1 items-center">
                                                @foreach($data['badges'] as $badge)
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-gray-700 text-gray-300 border border-gray-600">
                                                        {{ $badge }}
                                                    </span>
                                                @endforeach
                                                @if($data['overflow'] > 0)
                                                    <span class="text-[10px] text-gray-500">+{{ $data['overflow'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($product->variant_badges['total_count'] > 0)
                                <span class="text-xs text-gray-500">{{ $product->variant_badges['total_count'] }} variantes (sin atributos)</span>
                            @else
                                <span class="text-xs text-gray-500 italic">Sin variantes</span>
                            @endif
                        </x-table.td>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $product->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>

                            <x-table.dropdown-action-item :href="route('products.show', $product)" icon="fa-eye"
                                title="Ver catálogo completo">
                                Ver Detalle
                            </x-table.dropdown-action-item>

                            @if ($product->isEditable())
                                <x-table.dropdown-action-item :href="route('products.edit', $product)" icon="fa-edit"
                                    title="Modificar datos del producto">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endif

                            <x-table.dropdown-action-delete 
                                :action="route('products.destroy', $product)"
                                :message="$product->isArchived() ? '¿Rehabilitar este producto para permitir ventas y stock?' : '¿Descontinuar este producto? Ya no aparecerá en ventas.'"
                                :title="$product->isArchived() ? 'Rehabilitar' : 'Descontinuar'"
                                :icon="$product->isArchived() ? 'fa-undo' : 'fa-ban'"
                            />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-boxes fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron productos</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection
