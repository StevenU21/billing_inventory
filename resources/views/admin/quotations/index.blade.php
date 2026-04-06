@extends('layouts.app')
@section('title', 'Cotizaciones')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :items="[['label' => 'Módulo de Ventas', 'href' => '#', 'icon' => 'fa-home'], ['label' => 'Cotizaciones']]" />

        <x-page-header title="Cotizaciones" subtitle="Lista y exporta tus cotizaciones." icon="fa-file-invoice-dollar"
            :action-href="route('admin.quotations.create')" action-label="Nueva Cotización"
            action-permission="create quotations">
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card action="{{ route('admin.quotations.index') }}">
            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-medium text-gray-400 mb-1">Buscar Cliente</label>
                <x-autocomplete name="filter[search]" :value="request('filter.search')"
                    url="{{ route('admin.quotations.autocomplete') }}" placeholder="Nombre, cédula..." id="search" />
            </div>
            <x-filter-card.select name="filter[status]" label="Estado" :options="['pending' => 'Pendiente', 'accepted' => 'Aceptada', 'rejected' => 'Cancelada']" :selected="request('filter.status')" placeholder="Todos"
                class="col-span-6 lg:col-span-2" />
            <x-inputs.range-datepicker name-from="filter[from]" name-to="filter[to]" label-from="Desde" label-to="Hasta"
                :value-from="request('filter.from')" :value-to="request('filter.to')" class="col-span-12 lg:col-span-4" />
            <div class="col-span-6 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>

        </x-filter-card>

        <div class="mt-4">
            <x-table :resource="$quotations">
                <x-slot name="thead">
                    <x-table.th>Folio</x-table.th>
                    <x-table.th>Fecha</x-table.th>
                    <x-table.th>Cliente</x-table.th>
                    <x-table.th class="text-center">Resumen</x-table.th>
                    <x-table.th class="text-center">Estado</x-table.th>
                    <x-table.th class="!text-right">Total</x-table.th>
                    <x-table.th class="text-center">Acciones</x-table.th>
                </x-slot>
                <x-slot name="tbody">
                    @foreach ($quotations as $quotation)
                        <x-table.tr>
                            <x-table.td-folio :id="$quotation->id" />

                            <x-table.td-text variant="muted" size="sm">
                                {{ $quotation->formatted_created_at }}
                            </x-table.td-text>

                            <x-table.td-stacked :top="$quotation->client?->full_name ?? '-'" :middle="'Atendido por: ' . ($quotation->user?->short_name ?? ($quotation->user?->name ?? '-'))"
                                top-class="text-gray-200 truncate max-w-[200px]" />

                            <x-table.td-summary :summary="$quotation->summary" :count="$quotation->quotation_details_count"
                                align="center" />

                            <x-table.td-badge :color="$quotation->status_color" :text="$quotation->status_label"
                                class="text-center justify-center" />

                            <x-table.td-text variant="highlight" align="right" font="mono" class="tabular-nums font-bold">
                                {{ $quotation->formatted_total }}
                            </x-table.td-text>

                            <x-table.dropdown-actions>
                                <x-table.dropdown-action-item :href="route('quotations.export.show', $quotation)"
                                    icon="fa-file-pdf" title="Proforma PDF" data-no-loader="true">
                                    Ver Proforma
                                </x-table.dropdown-action-item>

                                @if ($quotation->isPending)
                                    <form action="{{ route('admin.quotations.accept', $quotation) }}" method="POST"
                                        onsubmit="return confirm('¿Está seguro de aceptar esta cotización? Esta acción la convertirá en una venta.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-sm text-emerald-400 hover:bg-emerald-500/10 transition-colors flex items-center gap-3 rounded-md"
                                            title="Aceptar cotización">
                                            <i class="fas fa-check w-4"></i>
                                            <span>Aceptar</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.quotations.cancel', $quotation) }}" method="POST"
                                        onsubmit="return confirm('¿Está seguro de cancelar esta cotización?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors flex items-center gap-3 rounded-md"
                                            title="Cancelar cotización">
                                            <i class="fas fa-times w-4"></i>
                                            <span>Cancelar</span>
                                        </button>
                                    </form>
                                @endif
                            </x-table.dropdown-actions>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </div>
    </div>
@endsection