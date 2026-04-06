@extends('layouts.app')
@section('title', 'Impuestos')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Impuestos'" />

        <x-page-header title="Impuestos" subtitle="Configura y administra los impuestos del sistema." icon="fa-percent"
            :action-href="route('taxes.create')" action-label="Crear impuesto" action-permission="create taxes">
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card :action="route('taxes.index')">
            <div class="col-span-full">
                <x-filter-card.search />
            </div>
        </x-filter-card>

        <x-table :resource="$taxes">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Porcentaje</x-table.th>
                <x-table.th>Creación</x-table.th>
                <x-table.th>Actualización</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($taxes as $tax)
                    <x-table.tr>
                        <x-table.td-folio :id="$tax->id" />

                        <x-table.td-text variant="highlight">
                            {{ $tax->name }}
                        </x-table.td-text>

                        <x-table.td-text font="mono" variant="muted">
                            {{ $tax->percentage }}%
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $tax->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $tax->formatted_updated_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>
                            @can('read taxes')
                                <x-table.dropdown-action-item :href="route('taxes.show', $tax)" icon="fa-eye">
                                    Ver
                                </x-table.dropdown-action-item>
                            @endcan
                            @can('update taxes')
                                <x-table.dropdown-action-item :href="route('taxes.edit', $tax)" icon="fa-edit">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endcan

                            <x-table.dropdown-action-delete :action="route('taxes.destroy', $tax)"
                                message="¿Estás seguro de eliminar este impuesto?" />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="6" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-percent fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron impuestos</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection