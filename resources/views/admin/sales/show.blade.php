@extends('layouts.app')
@section('title', 'Venta #' . $sale->id)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Ventas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Ventas', 'href' => route('admin.sales.index')],
            ['label' => 'Detalles'],
        ]" />

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Venta #{{ $sale->id }}
                    </h1>
                    <x-badge :color="$sale->sale_type->color()" :text="$sale->sale_type->label()" />
                    <x-badge :color="$sale->account_status->color()" :text="$sale->account_status->label()" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Cliente: {{ $sale->client->full_name ?? '-' }} • Fecha: {{ $sale->formatted_created_at }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-link href="{{ route('admin.sales.index') }}" variant="secondary" icon="fas fa-arrow-left">
                    Volver
                </x-link>

                <x-link href="{{ route('sales.export.show', $sale) }}" variant="secondary" icon="fa-file-pdf"
                    data-no-loader="true">
                    Ver PDF
                </x-link>

                <x-link href="{{ route('sales.export.receipt', $sale) }}" variant="primary" icon="fas fa-receipt" data-no-loader="true">
                    Imprimir Factura
                </x-link>
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Subtotal --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <i class="fas fa-money-bill-alt text-gray-600 dark:text-gray-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Subtotal</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $sale->formatted_subtotal }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Discount --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="fas fa-percent text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Descuento</p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            -{{ $sale->formatted_discount_total }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tax --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-landmark text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Impuesto (IVA)</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ $sale->formatted_tax_amount }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="fas fa-cash-register text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total a Pagar</p>
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                            {{ $sale->formatted_total }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Details Table with Integrated Info --}}
        <div x-data="{ showPanel: false }">
            <x-table>
                <x-slot:header>
                    <x-table.header title="Detalles de la Venta" icon="fa-shopping-cart" :collapsible="true"
                        collapsibleLabel="Ver Info de la Venta" collapsibleLabelOpen="Ocultar Info"
                        collapsibleIcon="fa-info-circle" />
                </x-slot:header>

                <x-slot:info>
                    <x-table.info-panel :cols="4">
                        <x-table.info-item label="Cliente:" icon="fa-user" :value="$sale->client->full_name ?? '-'" />
                        <x-table.info-item label="Método de Pago:" icon="fa-money-check-alt"
                            :value="$sale->paymentMethod?->name ?? '-'" />
                        <x-table.info-item label="Vendedor:" icon="fa-user-circle" :value="$sale->user?->short_name ?? '-'" />
                        <x-table.info-item label="Fecha:" icon="fa-calendar-alt" :value="$sale->formatted_created_at" />
                    </x-table.info-panel>
                </x-slot:info>

                <x-slot:thead>
                    <x-table.th>Producto</x-table.th>
                    <x-table.th class="text-right">Cant.</x-table.th>
                    <x-table.th class="text-right">Precio Unit.</x-table.th>
                    <x-table.th class="text-right">Desc.</x-table.th>
                    <x-table.th class="text-right">IVA</x-table.th>
                    <x-table.th class="text-right">Total</x-table.th>
                </x-slot:thead>

                <x-slot:tbody>
                    @forelse($sale->saleDetails as $detail)
                        <x-table.tr>
                            <x-table.td>
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $detail->product_name }}</div>
                                @if($detail->variant_display !== 'Simple')
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $detail->variant_display }}
                                    </div>
                                @endif
                            </x-table.td>
                            <x-table.td-text align="right" font="mono">
                                {{ $detail->formatted_quantity }}
                                <span
                                    class="text-xs text-gray-500 font-normal ml-1">{{ $detail->productVariant->product->unitMeasure->symbol ?? '' }}</span>
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono">
                                {{ $detail->formatted_unit_price }}
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono" variant="success">
                                {{ $detail->formatted_discount }}
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
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Sin detalles registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </x-slot:tbody>
            </x-table>
        </div>
    </div>
@endsection