@extends('layouts.app')
@section('title', 'Sesión de Caja #' . $session->id)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Caja Registradora', 'href' => route('admin.cash-register.index')],
            ['label' => 'Sesión #' . $session->id],
        ]" />

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Sesión de Caja {{ $session->ref }}
                    </h1>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $session->is_open ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $session->status_label }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cajero: {{ $session->user?->short_name ?? '-' }} • {{ $session->formatted_opened_at }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-link href="{{ route('admin.cash-register.index') }}" variant="secondary" icon="fas fa-arrow-left">
                    Volver
                </x-link>
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            {{-- Opening Balance --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-door-open text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Apertura</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $session->formatted_opening_balance }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Income --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-arrow-down text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Entradas Efectivo</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            +{{ $session->total_income->formatTo('es_NI') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Expense --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-arrow-up text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Salidas Efectivo</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                            -{{ $session->total_expense->formatTo('es_NI') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Expected Balance --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <i class="fas fa-calculator text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Balance Esperado</p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $session->formatted_expected_closing_balance }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Non-Cash Income (Replaces Status) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $session->formatted_non_cash_sales }}
                    </div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Entradas No Efectivo
                    </div>
                </div>
            </div>
        </div>

        {{-- Movements Table (Full Width) --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Movimientos de caja en efectivo
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <x-table.th class="text-left">Hora</x-table.th>
                            <x-table.th class="text-left">Usuario</x-table.th>
                            <x-table.th class="text-center">Tipo</x-table.th>
                            <x-table.th class="text-left">Descripción</x-table.th>
                            <x-table.th class="text-right">Monto</x-table.th>
                            <x-table.th class="text-right">Balance</x-table.th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $movement)
                            <x-table.tr>
                                <x-table.td-text variant="muted" class="whitespace-nowrap">
                                    {{ $movement->formatted_movement_at }}
                                </x-table.td-text>
                                <x-table.td-text>
                                    {{ $movement->user_name }}
                                </x-table.td-text>
                                <x-table.td-badge :color="$movement->type_badge_color" :text="$movement->type_label" />
                                <x-table.td>
                                    @php
                                        $refRoute = match ($movement->reference_type) {
                                            'App\\Models\\Sale' => Route::has('admin.sales.show') ? route('admin.sales.show', $movement->reference_id) : null,
                                            'App\\Models\\Purchase' => Route::has('admin.purchases.show') ? route('admin.purchases.show', $movement->reference_id) : null,
                                            default => null,
                                        };
                                    @endphp

                                    @if($refRoute)
                                        <a href="{{ $refRoute }}"
                                            class="group inline-flex items-center font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors text-sm">
                                            {{ $movement->reference_display }}
                                            <i
                                                class="fas fa-external-link-alt ml-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </a>
                                        @if($movement->description)
                                            <span class="block text-xs text-gray-500 mt-0.5">{{ $movement->description }}</span>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $movement->description ?? $movement->reference_display ?? '-' }}
                                        </span>
                                    @endif
                                </x-table.td>
                                <x-table.td-text :variant="$movement->is_income ? 'success' : 'error'" align="right" font="mono"
                                    class="whitespace-nowrap tabular-nums">
                                    {{ $movement->formatted_signed_amount }}
                                </x-table.td-text>
                                <x-table.td-text align="right" font="mono" class="whitespace-nowrap tabular-nums">
                                    {{ $movement->formatted_balance_after }}
                                </x-table.td-text>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No hay movimientos registrados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(!$session->is_open)
            {{-- Closed Session Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-6">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información de Cierre
                    </h3>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cerrado por:</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ $session->closedByUser?->short_name ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Fecha de cierre:</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ $session->formatted_closed_at ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Balance esperado:</span>
                        <span class="text-sm font-mono font-medium text-gray-800 dark:text-gray-100">
                            {{ $session->formatted_expected_closing_balance }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Balance real:</span>
                        <span class="text-sm font-mono font-medium text-gray-800 dark:text-gray-100">
                            {{ $session->formatted_actual_closing_balance }}
                        </span>
                    </div>
                    @if($session->has_difference)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Diferencia:</span>
                            <span
                                class="text-sm font-mono font-bold {{ $session->difference->isPositive() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $session->formatted_difference }} ({{ $session->difference_type }})
                            </span>
                        </div>
                    @endif
                    @if($session->notes)
                        <div class="md:col-span-2 lg:col-span-4 pt-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Notas:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                                {{ $session->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection