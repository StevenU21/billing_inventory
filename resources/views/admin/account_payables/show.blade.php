@extends('layouts.app')
@section('title', 'Cuenta por Pagar #' . $ap->id)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Cuentas por Pagar', 'href' => route('admin.account_payables.index')],
            ['label' => 'Detalles'],
        ]" />

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Cuenta por Pagar #{{ $ap->id }}
                    </h1>
                    <x-badge :color="$ap->status_color" :text="$ap->status_label" />
                    @if (!empty($ap->condition_label))
                        <x-badge :color="$ap->condition_color" :text="$ap->condition_label" />
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Proveedor: {{ $ap->supplier_label }} • Fecha Compra: {{ $ap->purchase?->formatted_created_at ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-link href="{{ route('admin.account_payables.index') }}" variant="secondary" icon="fas fa-arrow-left">
                    Volver
                </x-link>
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Total Debt --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="fas fa-file-invoice-dollar text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Deuda</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $ap->formatted_total_amount }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Paid Amount --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Abonado</p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $ap->formatted_amount_paid }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Balance --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-balance-scale text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Pendiente</p>
                        <p class="text-xl font-bold {{ $ap->balance_text_class }}">
                            {{ $ap->formatted_balance }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Purchase Details Table --}}
            <div class="lg:col-span-2 space-y-6">
                <div x-data="{ showPanel: false }">
                    <x-table>
                        <x-slot:header>
                            <x-table.header title="Detalles de Compra (Origen)" icon="fa-shopping-cart" :collapsible="true"
                                collapsibleLabel="Ver Info de la Cuenta" collapsibleLabelOpen="Ocultar Info"
                                collapsibleIcon="fa-info-circle" />
                        </x-slot:header>

                        <x-slot:info>
                            <x-table.info-panel :cols="4">
                                <x-table.info-item label="Proveedor:" icon="fa-truck" :value="$ap->supplier_label" />
                                <x-table.info-item label="Usuario Compra:" icon="fa-user-circle"
                                    :value="$ap->purchase?->user?->short_name ?? '-'" />
                                <x-table.info-item label="Fecha Compra:" icon="fa-calendar-alt"
                                    :value="$ap->purchase?->formatted_created_at ?? '-'" />
                                <x-table.info-item label="Estado:" icon="fa-info-circle" :value="$ap->status_label" />
                            </x-table.info-panel>
                        </x-slot:info>

                        <x-slot:thead>
                            <x-table.th>Producto</x-table.th>
                            <x-table.th class="text-right">Cant.</x-table.th>
                            <x-table.th class="text-right">Costo Unit.</x-table.th>
                            <x-table.th class="text-right">IVA</x-table.th>
                            <x-table.th class="text-right">Total</x-table.th>
                        </x-slot:thead>

                        <x-slot:tbody>
                            @forelse ($ap->purchase?->details ?? [] as $detail)
                                <x-table.tr>
                                    <x-table.td>
                                        <div class="font-medium text-gray-800 dark:text-gray-100">{{ $detail->product_name }}
                                        </div>
                                        @if ($detail->variant_display !== 'Simple')
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $detail->variant_display }}
                                            </div>
                                        @endif
                                    </x-table.td>
                                    <x-table.td-text align="right" font="mono">
                                        {{ $detail->formatted_quantity }}
                                    </x-table.td-text>
                                    <x-table.td-text align="right" font="mono">
                                        {{ $detail->formatted_unit_price }}
                                    </x-table.td-text>
                                    <x-table.td-text align="right" font="mono" variant="muted">
                                        {{ $detail->formatted_tax }}
                                    </x-table.td-text>
                                    <x-table.td-text align="right" font="mono" variant="highlight">
                                        {{ $detail->formatted_total }}
                                    </x-table.td-text>
                                </x-table.tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Sin detalles registrados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </x-slot:tbody>
                    </x-table>
                </div>
            </div>

            {{-- Payment History (Side Panel or Bottom) --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-gray-400"></i>
                        Historial de Pagos
                    </h3>

                    @if($ap->payments->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($ap->payments as $payment)
                                <div
                                    class="flex items-start justify-between pb-3 border-b dark:border-gray-700 last:border-0 last:pb-0">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ $payment->paymentMethod->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $payment->formatted_created_at }}
                                        </div>
                                        @if($payment->notes)
                                            <div class="text-xs text-gray-500 italic mt-1">{{ $payment->notes }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $payment->formatted_amount }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $payment->user?->short_name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-money-bill-wave-alt mb-2 text-2xl opacity-50"></i>
                            <p class="text-sm">No hay pagos registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection