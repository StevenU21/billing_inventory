@extends('layouts.app')
@section('title', 'Marcas')

@section('content')

    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Marcas'" />

        <x-page-header title="Marcas" subtitle="Gestiona las marcas de tus productos." icon="fa-tags"
            :action-href="route('brands.create')" action-label="Crear marca" action-permission="create brands">
        </x-page-header>

        <!-- Mensajes de éxito -->
        <div class="mt-4">
            <x-session-message />
        </div>
        <!-- Fin mensajes de éxito -->

        <x-filter-card :action="route('brands.index')">
            <div class="col-span-12 lg:col-span-3">
                <x-filter-card.search />
            </div>

            <x-filter-card.select name="filter[category_id]" label="Categoría" :options="$categories"
                :selected="request('filter.category_id')" placeholder="Todas" class="col-span-6 lg:col-span-2" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <x-table :resource="$brands">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Categoría</x-table.th>
                <x-table.th>Descripción</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($brands as $brand)
                    <x-table.tr>
                        <x-table.td-folio :id="$brand->id" />

                        <x-table.td-text variant="highlight">
                            {{ $brand->name }}
                        </x-table.td-text>

                        <x-table.td-text>
                            {{ $brand->category->name ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $brand->description ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $brand->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>
                            @can('read brands')
                                <x-table.dropdown-action-item :href="route('brands.show', $brand)" icon="fa-eye">
                                    Ver
                                </x-table.dropdown-action-item>
                            @endcan
                            @can('update brands')
                                <x-table.dropdown-action-item :href="route('brands.edit', $brand)" icon="fa-edit">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endcan

                            <x-table.dropdown-action-delete :action="route('brands.destroy', $brand)"
                                message="¿Estás seguro de eliminar esta marca?" />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="6" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-tags fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron marcas</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection