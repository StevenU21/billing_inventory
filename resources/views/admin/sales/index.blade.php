@extends('layouts.app')
@section('title', 'Ventas')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Ventas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Listado de Ventas'],
        ]" />

        <x-page-header title="Ventas" subtitle="Gestión centralizada de transacciones."
            :action-href="route('admin.sales.create')" action-label="Nueva Venta" action-permission="create sales">
        </x-page-header>

        <x-filter-card action="{{ route('admin.sales.index') }}">

            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-medium text-gray-400 mb-1">Buscar Cliente</label>
                <x-autocomplete name="filter[search]" :value="request()->input('filter.search')"
                    url="{{ route('admin.autocomplete.clients') }}" placeholder="Nombre, cédula..." id="search" />
            </div>
            <x-filter-card.select name="filter[is_credit]" label="Condición" :options="['' => 'Todas', '0' => 'Contado', '1' => 'Crédito']" :selected="request()->input('filter.is_credit')" class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[payment_method_id]" label="Método" :options="['' => 'Todos'] + $methods->toArray()" :selected="request()->input('filter.payment_method_id')"
                class="col-span-6 lg:col-span-2" />

            <x-inputs.range-datepicker name-from="filter[from]" name-to="filter[to]" label-from="Desde" label-to="Hasta"
                :value-from="request()->input('filter.from')" :value-to="request()->input('filter.to')"
                class="col-span-12 lg:col-span-4" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-table :resource="$sales">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th>Cliente</x-table.th>
                <x-table.th class="text-left">Resumen</x-table.th>
                <x-table.th class="text-center">Condición</x-table.th>
                <x-table.th class="text-center">Estado</x-table.th>
                <x-table.th class="!text-right">Monto</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($sales as $sale)
                    <x-table.tr>
                        <x-table.td-folio :id="$sale->id" />

                        <x-table.td-text variant="muted" size="sm">
                            {{ $sale->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-stacked :top="$sale->client->full_name" :middle="'Atendido por: ' . $sale->user->short_name"
                            top-class="text-gray-200 truncate max-w-[200px]" />

                        <x-table.td-summary :summary="$sale->summary" :count="$sale->sale_details_count" />

                        <x-table.td-badge :color="$sale->sale_type->color()" :text="$sale->sale_type->label()" />

                        <x-table.td-badge :color="$sale->display_status->color()" :text="$sale->display_status->label()" />

                        <x-table.td-text variant="highlight" align="right" font="mono" class="tabular-nums font-bold">
                            {{ $sale->formatted_total }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>

                            <x-table.dropdown-action-item :href="route('admin.sales.show', $sale)" icon="fa-eye"
                                title="Detalle de la venta">
                                Ver Detalle
                            </x-table.dropdown-action-item>

                            <x-table.dropdown-action-item :href="route('sales.export.receipt', $sale)" icon="fa-receipt"
                                title="Imprimir ticket de venta" data-no-loader="true">
                                Ver Ticket
                            </x-table.dropdown-action-item>

                            @if(isset($latestSaleId) && $sale->id === $latestSaleId && !$sale->is_cancelled)
                                <x-table.dropdown-action-delete :action="route('admin.sales.destroy', $sale)"
                                    message="¿Anular venta? El stock se devolverá al inventario automáticamente." title="Anular"
                                    :can="['delete', $sale]" />
                            @endif

                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <tr>
                        <x-table.td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-inbox fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron ventas</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection