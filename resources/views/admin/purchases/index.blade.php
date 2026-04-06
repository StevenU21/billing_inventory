@extends('layouts.app')
@section('title', 'Compras')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Listado de Compras'],
        ]" />

        <x-page-header title="Compras" subtitle="Gestión centralizada de compras a proveedores."
            :action-href="route('purchases.create')" action-label="Nueva Compra" action-permission="create purchases">
        </x-page-header>

        <x-filter-card action="{{ route('purchases.index') }}">

            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-medium text-gray-400 mb-1">Buscar Proveedor</label>
                <x-autocomplete name="filter[search]" :value="request()->input('filter.search')"
                    url="{{ route('admin.autocomplete.suppliers') }}" placeholder="Nombre, cédula, RUC..." id="search" />
            </div>

            <x-filter-card.select name="filter[payment_method_id]" label="Método" :options="$methods->toArray()"
                :selected="request()->input('filter.payment_method_id')" placeholder="Todos"
                class="col-span-6 lg:col-span-2" />

            <x-filter-card.select name="filter[status]" label="Estado" :options="$statuses->toArray()"
                :selected="request()->input('filter.status')" placeholder="Todos" class="col-span-6 lg:col-span-2" />

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

        <x-table :resource="$purchases">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th>Proveedor</x-table.th>
                <x-table.th class="text-left">Resumen</x-table.th>
                <x-table.th class="text-center">Condición</x-table.th>
                <x-table.th class="text-center">Estado</x-table.th>
                <x-table.th class="!text-right">Monto</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($purchases as $purchase)
                    <x-table.tr>
                        <x-table.td-folio :id="$purchase->id" />

                        <x-table.td-text variant="muted" size="sm">
                            {{ $purchase->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-stacked :top="$purchase->entity?->full_name ?? '-'" :middle="'Registrado por: ' . ($purchase->user?->short_name ?? '-')" top-class="text-gray-200 truncate max-w-[200px]" />

                        <x-table.td-summary :summary="$purchase->summary" :count="$purchase->details_count" />

                        <x-table.td-badge :color="$purchase->purchase_type->color()"
                            :text="$purchase->purchase_type->label()" />

                        <x-table.td-badge :color="$purchase->status->color()" :text="$purchase->status->label()" />

                        <x-table.td-text variant="highlight" align="right" font="mono" class="tabular-nums font-bold">
                            {{ $purchase->formatted_total }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>

                            <x-table.dropdown-action-item :href="route('purchases.show', $purchase)" icon="fa-eye"
                                title="Detalle de la compra">
                                Ver Detalle
                            </x-table.dropdown-action-item>

                            <x-table.dropdown-action-item :href="route('purchases.export.show', $purchase)"
                                icon="fa-file-pdf" title="Compra PDF" data-no-loader="true">
                                Ver Compra
                            </x-table.dropdown-action-item>

                            @if($purchase->status === \App\Enums\PurchaseStatus::Draft)
                                <x-table.dropdown-action-item :href="route('purchases.edit', $purchase)" icon="fa-pencil-alt"
                                    title="Editar borrador" :can="['update', $purchase]">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endif

                            @can('update', $purchase)
                                @if($purchase->status === \App\Enums\PurchaseStatus::Draft || $purchase->status === \App\Enums\PurchaseStatus::Ordered)
                                    <form action="{{ route('purchases.receive', $purchase) }}" method="POST" class="block w-full">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-sm text-emerald-400 hover:bg-emerald-500/10 transition-colors flex items-center gap-3 rounded-md"
                                            onclick="return confirm('¿Confirmar recepción de mercadería? Esto actualizará el inventario.')">
                                            <i class="fas fa-check-circle w-4"></i>
                                            <span>Recibir / Aprobar</span>
                                        </button>
                                    </form>
                                @endif
                            @endcan

                            @if($purchase->status === \App\Enums\PurchaseStatus::Draft)
                                <x-table.dropdown-action-delete :action="route('purchases.destroy', $purchase)"
                                    message="¿Eliminar borrador de compra? Esta acción es irreversible." title="Eliminar Borrador"
                                    :can="['destroy', $purchase]" />
                            @endif
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <tr>
                        <x-table.td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-inbox fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron compras</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection