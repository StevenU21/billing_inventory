@extends('layouts.app')
@section('title', 'Unidades de Medida')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Unidades de Medida'" />

        <x-page-header title="Unidades de Medida" subtitle="Configura las unidades y abreviaturas." icon="fa-balance-scale"
            :action-href="route('unit_measures.create')" action-label="Crear unidad de medida"
            action-permission="create unit_measures">
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card :action="route('unit_measures.index')">
            <div class="col-span-full">
                <x-filter-card.search />
            </div>
        </x-filter-card>

        <x-table :resource="$unitMeasures">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Abreviatura</x-table.th>
                <x-table.th>Descripción</x-table.th>
                <x-table.th>Permite decimales</x-table.th>
                <x-table.th>Creación</x-table.th>
                <x-table.th>Actualización</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($unitMeasures as $unitMeasure)
                    <x-table.tr>
                        <x-table.td-folio :id="$unitMeasure->id" />

                        <x-table.td-text variant="highlight">
                            {{ $unitMeasure->name }}
                        </x-table.td-text>

                        <x-table.td-text font="mono" variant="muted">
                            {{ $unitMeasure->abbreviation ?? '-' }}
                        </x-table.td-text>


                        <x-table.td-text variant="muted" size="sm">
                            {{ $unitMeasure->description ?? '-' }}
                        </x-table.td-text>

                        <x-table.td>
                            @if($unitMeasure->allows_decimals)
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    <i class="fas fa-check mr-1"></i> Sí
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                    <i class="fas fa-times mr-1"></i> No
                                </span>
                            @endif
                        </x-table.td>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $unitMeasure->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $unitMeasure->formatted_updated_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>
                            @can('read unit_measures')
                                <x-table.dropdown-action-item :href="route('unit_measures.show', $unitMeasure)" icon="fa-eye">
                                    Ver
                                </x-table.dropdown-action-item>
                            @endcan
                            @can('update unit_measures')
                                <x-table.dropdown-action-item :href="route('unit_measures.edit', $unitMeasure)" icon="fa-edit">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endcan

                            <x-table.dropdown-action-delete :action="route('unit_measures.destroy', $unitMeasure)"
                                message="¿Estás seguro de eliminar esta unidad de medida?" />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-balance-scale fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron unidades de medida</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection