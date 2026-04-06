@extends('layouts.app')
@section('title', 'Compra #' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT))

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Compras', 'href' => route('purchases.index')],
            ['label' => 'Detalles'],
        ]" />

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Compra #{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}
                    </h1>
                    <x-badge :color="$purchase->purchase_type->color()" :text="$purchase->purchase_type->label()" />
                    <x-badge :color="$purchase->status->color()" :text="$purchase->status->label()" />
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Proveedor: {{ $purchase->entity?->short_name ?? '-' }} • Fecha: {{ $purchase->formatted_created_at }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-link href="{{ route('purchases.index') }}" variant="secondary" icon="fas fa-arrow-left" >
                    Volver
                </x-link>

                <x-link href="{{ route('purchases.export.show', $purchase) }}" variant="secondary" icon="fa-file-pdf" data-no-loader="true">
                    Ver PDF
                </x-link>
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Subtotal --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <i class="fas fa-money-bill-alt text-gray-600 dark:text-gray-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Subtotal</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $purchase->formatted_sub_total }}
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
                            {{ $purchase->formatted_tax_amount }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                            {{ $purchase->formatted_total }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Purchase Details Table with Integrated Info --}}
        <div x-data="{ showPanel: false }">
            <x-table>
                <x-slot:header>
                    <x-table.header
                        title="Detalles de la Compra"
                        icon="fa-shopping-cart"
                        :collapsible="true"
                        collapsibleLabel="Ver Info de la Compra"
                        collapsibleLabelOpen="Ocultar Info"
                        collapsibleIcon="fa-info-circle"
                    />
                </x-slot:header>

                <x-slot:info>
                    <x-table.info-panel :cols="5">
                        <x-table.info-item label="Proveedor:" icon="fa-truck" :value="$purchase->entity?->short_name ?? '-'" />
                        <x-table.info-item label="Referencia:" icon="fa-hashtag" font="mono" :value="$purchase->reference ?: '—'" />
                        <x-table.info-item label="Método de Pago:" icon="fa-money-check-alt" :value="$purchase->paymentMethod?->name ?? '-'" />
                        <x-table.info-item label="Usuario:" icon="fa-user-circle" :value="$purchase->user?->short_name ?? '-'" />
                        <x-table.info-item label="Fecha:" icon="fa-calendar-alt" :value="$purchase->formatted_created_at" />
                    </x-table.info-panel>
                </x-slot:info>

                <x-slot:thead>
                    <x-table.th>Producto</x-table.th>
                    <x-table.th>Variante</x-table.th>
                    <x-table.th class="text-right">Cant.</x-table.th>
                    <x-table.th class="text-right">P. Unit</x-table.th>
                    <x-table.th class="text-right">Importe</x-table.th>
                </x-slot:thead>

                <x-slot:tbody>
                    @forelse($purchase->details as $detail)
                        @php
                            $variant = $detail->productVariant;
                            $product = $variant?->product;
                            $variantLabel = $detail->variant_display;
                        @endphp
                        <x-table.tr>
                            <x-table.td>
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $product?->name ?? 'Producto eliminado' }}</div>
                            </x-table.td>
                            <x-table.td>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $variantLabel }}</div>
                            </x-table.td>
                            <x-table.td-text align="right" font="mono">
                                {{ (float) $detail->quantity }}
                                <span class="text-xs text-gray-500 font-normal ml-1">{{ $detail->productVariant->product->unitMeasure->symbol ?? '' }}</span>
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono">
                                {{ $detail->formatted_unit_price }}
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono" variant="highlight">
                                {{ $detail->formatted_amount }}
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
@endsection
