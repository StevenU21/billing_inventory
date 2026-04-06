@extends('layouts.app')
@section('title', 'Sesiones de Caja')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Caja Registradora'],
        ]" />

        <x-page-header title="Caja Registradora" subtitle="Gestión de sesiones y movimientos de caja."
            :action-href="route('admin.cash-register.create')" action-label="Abrir Caja"
            action-permission="open cash_register">
        </x-page-header>

        <x-filter-card action="{{ route('admin.cash-register.index') }}">
            <x-filter-card.select name="filter[status]" label="Estado" :options="['' => 'Todos', 'open' => 'Abierta', 'closed' => 'Cerrada', 'suspended' => 'Suspendida']" :selected="request()->input('filter.status')"
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

        <x-table :resource="$sessions">
            <x-slot name="thead">
                <x-table.th>ID</x-table.th>
                <x-table.th>Cajero</x-table.th>
                <x-table.th>Apertura</x-table.th>
                <x-table.th>Cierre</x-table.th>
                <x-table.th class="!text-right">Monto Inicial</x-table.th>
                <x-table.th class="!text-right">Balance Esperado</x-table.th>
                <x-table.th class="text-center">Movimientos</x-table.th>
                <x-table.th class="text-center">Estado</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>

            <x-slot name="tbody">
                @forelse($sessions as $session)
                    <x-table.tr>
                        <x-table.td-folio :id="$session->id" />

                        <x-table.td-stacked :top="$session->user?->short_name ?? '-'" :middle="'Abierta por: ' . ($session->openedByUser?->short_name ?? '-')" top-class="text-gray-200" />

                        <x-table.td-text variant="muted" size="sm">
                            {{ $session->formatted_opened_at ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            <div class="flex flex-col">
                                <span>{{ $session->formatted_closed_at ?? '-' }}</span>
                                @if($session->closed_at && $session->status->value === 'closed')
                                    @if($session->has_difference)
                                        <span class="text-[10px] text-red-500 font-bold flex items-center gap-1">
                                            <i class="fas fa-exclamation-triangle"></i> Descuadre
                                        </span>
                                    @else
                                        <span class="text-[10px] text-green-500 font-bold flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Cuadre OK
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </x-table.td-text>

                        <x-table.td-text variant="highlight" align="right" font="mono" class="tabular-nums">
                            {{ $session->formatted_opening_balance }}
                        </x-table.td-text>

                        <x-table.td-text variant="highlight" align="right" font="mono" class="tabular-nums font-bold">
                            {{ $session->formatted_expected_closing_balance }}
                        </x-table.td-text>

                        <x-table.td-text align="center" variant="muted">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-exchange-alt text-xs"></i>
                                {{ $session->movements_count ?? 0 }}
                            </span>
                        </x-table.td-text>

                        <x-table.td-badge :color="$session->status_badge_color" :text="$session->status_label" />

                        <x-table.dropdown-actions>
                            <x-table.dropdown-action-item :href="route('admin.cash-register.show', $session)" icon="fa-eye"
                                title="Ver detalle de la sesión">
                                Ver Detalle
                            </x-table.dropdown-action-item>

                            @if($session->is_open)
                                <x-table.dropdown-action-item :href="route('admin.cash-register.movement-form', $session)"
                                    icon="fa-exchange-alt" title="Registrar movimiento de caja">
                                    Registrar Movimiento
                                </x-table.dropdown-action-item>

                                <x-table.dropdown-action-item :href="route('admin.cash-register.close-form', $session)"
                                    icon="fa-cash-register" title="Cerrar caja">
                                    Cerrar Caja
                                </x-table.dropdown-action-item>
                            @endif
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <tr>
                        <x-table.td colspan="9" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-cash-register fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No hay sesiones de caja</p>
                                <p class="text-sm">Abre una nueva sesión para comenzar</p>
                            </div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection