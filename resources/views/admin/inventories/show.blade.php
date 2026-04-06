@extends('layouts.app')
@section('title', 'Inventario - ' . ($inventory->productVariant->product->name ?? 'Producto'))

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Modulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Inventario', 'href' => route('inventories.index')],
            ['label' => $inventory->productVariant->product->name ?? 'Detalle'],
        ]" />

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        {{ $inventory->productVariant->product->name ?? 'Producto' }}
                    </h1>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $inventory->is_low_stock ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' }}">
                        {{ $inventory->stock_status_label }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Variante: {{ $inventory->variant_display }} • SKU: {{ $inventory->productVariant->sku ?? '-' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-link href="{{ route('inventories.index') }}" variant="secondary" icon="fas fa-arrow-left">
                    Volver
                </x-link>
                @can('update', $inventory)
                    <x-link href="{{ route('inventories.edit', $inventory) }}" variant="primary" icon="fas fa-edit">
                        Ajustar Stock
                    </x-link>
                @endcan
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            {{-- Current Stock --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-boxes text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Stock Actual</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $inventory->formatted_stock }}
                            <span
                                class="text-xs text-gray-500 font-normal">{{ $inventory->productVariant->product->unitMeasure->symbol ?? '' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Entries --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <i class="fas fa-arrow-down text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Entradas</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            +{{ number_format($totalIn, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Exits --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-arrow-up text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Salidas</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                            -{{ number_format($totalOut, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Average Cost --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <i class="fas fa-calculator text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Costo Promedio</p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $inventory->average_cost?->formatTo('es_NI') ?? 'C$ 0.00' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Inventory Value --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $inventory->formatted_value_in_warehouse }}
                    </div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Valor en Almacén
                    </div>
                </div>
            </div>
        </div>

        {{-- Movements Table with Integrated Product Info --}}
        <div x-data="{ showPanel: false }">
            <x-table :resource="$movements">
                <x-slot:header>
                    <x-table.header 
                        title="Movimientos de Inventario" 
                        icon="fa-exchange-alt"
                        :collapsible="true"
                        collapsibleLabel="Ver Info del Producto"
                        collapsibleLabelOpen="Ocultar Info"
                        collapsibleIcon="fa-info-circle"
                    />
                </x-slot:header>

                <x-slot:info>
                    <x-table.info-panel :cols="4">
                        <x-table.info-item label="Categoría:" icon="fa-tag" :value="$inventory->productVariant->product->brand->category->name ?? '-'" />
                        <x-table.info-item label="Marca:" icon="fa-copyright" :value="$inventory->productVariant->product->brand->name ?? '-'" />
                        <x-table.info-item label="Stock Mínimo:" icon="fa-exclamation-triangle" font="mono" :value="$inventory->formatted_min_stock" />
                        <x-table.info-item label="Precio de Venta:" icon="fa-dollar-sign" font="mono" :value="$inventory->productVariant->price?->formatTo('es_NI') ?? '-'" />
                    </x-table.info-panel>
                </x-slot:info>

                <x-slot:thead>
                    <x-table.th class="text-left">Fecha</x-table.th>
                    <x-table.th class="text-left">Usuario</x-table.th>
                    <x-table.th class="text-center">Tipo</x-table.th>
                    <x-table.th class="text-left">Notas</x-table.th>
                    <x-table.th class="text-right">Cantidad</x-table.th>
                    <x-table.th class="text-right">Stock Antes</x-table.th>
                    <x-table.th class="text-right">Stock Después</x-table.th>
                </x-slot:thead>

                <x-slot:tbody>
                    @forelse($movements as $movement)
                        <x-table.tr>
                            <x-table.td-text variant="muted" class="whitespace-nowrap">
                                {{ $movement->formatted_created_at }}
                            </x-table.td-text>
                            <x-table.td-text>
                                {{ $movement->user->name ?? '-' }}
                            </x-table.td-text>
                            <x-table.td-badge :color="$movement->movement_type_badge" :text="$movement->movement_type" />
                            <x-table.td>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $movement->notes ?? $movement->reference ?? '-' }}
                                </span>
                            </x-table.td>
                            <x-table.td-text :variant="$movement->type->multiplier() === 1 ? 'success' : 'error'"
                                align="right" font="mono" class="whitespace-nowrap tabular-nums">
                                {{ $movement->type->multiplier() === 1 ? '+' : '-' }}{{ $movement->formatted_quantity }}
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono" class="whitespace-nowrap tabular-nums">
                                {{ number_format((float) $movement->stock_after - ($movement->type->multiplier() * (float) $movement->quantity), 2) }}
                            </x-table.td-text>
                            <x-table.td-text align="right" font="mono" class="whitespace-nowrap tabular-nums">
                                {{ number_format((float) $movement->stock_after, 2) }}
                            </x-table.td-text>
                        </x-table.tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No hay movimientos registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </x-slot:tbody>
            </x-table>
        </div>
    </div>
@endsection