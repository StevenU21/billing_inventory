@extends('layouts.app')
@section('title', 'Producto: ' . $product->name)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Productos', 'href' => route('products.index')],
            ['label' => 'Detalle'],
        ]" />

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                @if($product->has_real_image)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                        class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shadow-sm">
                @else
                    <div
                        class="w-16 h-16 rounded-lg bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                        {{ $product->initials }}
                    </div>
                @endif
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                            {{ $product->name }}
                        </h1>
                        <x-badge :color="$product->status->color()" :text="$product->status->label()" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono">
                        {{ $product->code }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-link href="{{ route('products.index') }}" variant="secondary" icon="fas fa-arrow-left">
                    Volver
                </x-link>

                @if($product->isEditable())
                    <x-link href="{{ route('products.edit', $product) }}" variant="primary" icon="fas fa-edit">
                        Editar
                    </x-link>
                @endif
            </div>
        </div>

        <div class="mt-4">
            <x-session-message />
        </div>

        @php
            $totalInventoryValue = $product->variants->reduce(function ($carry, $variant) {
                $value = $variant->total_value_money;
                if (!$value)
                    return $carry;
                return $carry ? $carry->plus($value) : $value;
            });

            // Get all unique attribute names from variant badges accessor
            $attributeNames = array_keys($product->variant_badges['attributes'] ?? []);
        @endphp

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Variants Count --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="fas fa-layer-group text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Variantes</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                            {{ $product->variants->count() }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Stock --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-boxes text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Stock Total</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                            {{ number_format($product->variants->sum('stock_quantity'), 2, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Inventory Value --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Valor Inventario</p>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            <x-money :amount="$totalInventoryValue" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Last Update --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Última Actualización</p>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                            {{ $product->formatted_updated_at }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Variants Details Table with Integrated Info --}}
        <div x-data="{ showPanel: false }">
            <x-table :resource="$product->variants">
                <x-slot:header>
                    <x-table.header title="Variantes de Inventario" icon="fa-box-open" :collapsible="true"
                        collapsibleLabel="Ver Info del Producto" collapsibleLabelOpen="Ocultar Info"
                        collapsibleIcon="fa-info-circle" />
                </x-slot:header>

                <x-slot:info>
                    <x-table.info-panel :cols="5">
                        <x-table.info-item label="Categoría:" icon="fa-tag" :value="$product->brand->category->name ?? '-'" />
                        <x-table.info-item label="Marca:" icon="fa-copyright" :value="$product->brand->name ?? '-'" />
                        <x-table.info-item label="Impuesto:" icon="fa-percent" :value="$product->tax->name ?? 'Exento'" />
                        <x-table.info-item label="Medida:" icon="fa-ruler" :value="$product->unitMeasure->name ?? '-'" />
                        <x-table.info-item label="Código:" icon="fa-barcode" font="mono" :value="$product->code" />
                    </x-table.info-panel>
                </x-slot:info>

                <x-slot:thead>
                    <x-table.th>SKU</x-table.th>
                    @foreach($attributeNames as $label)
                        <x-table.th>{{ $label }}</x-table.th>
                    @endforeach
                    <x-table.th class="text-right">Existencia</x-table.th>
                    <x-table.th class="text-right">Precio Unit.</x-table.th>
                    <x-table.th class="text-right">Valor Total</x-table.th>
                </x-slot:thead>

                <x-slot:tbody>
                    @forelse($product->variants as $variant)
                        <x-table.tr>
                            <x-table.td-text font="mono" variant="muted" class="opacity-70">
                                {{ $variant->sku }}
                            </x-table.td-text>

                            @foreach($attributeNames as $key)
                                <x-table.td class="{{ $loop->first ? 'font-medium text-gray-800 dark:text-gray-200' : '' }}">
                                    {{ $variant->attributeValues->firstWhere('attribute.name', $key)->value ?? '-' }}
                                </x-table.td>
                            @endforeach

                            <x-table.td-text align="right" font="mono" class="tabular-nums">
                                {{ number_format($variant->stock_quantity, 2, '.', ',') }}
                            </x-table.td-text>

                            <x-table.td class="text-right tabular-nums font-mono">
                                <x-money :amount="$variant->current_unit_price_money" />
                            </x-table.td>

                            <x-table.td class="text-right tabular-nums font-bold font-mono text-gray-800 dark:text-gray-200">
                                <x-money :amount="$variant->total_value_money" />
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($attributeNames) + 4 }}"
                                class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-box-open fa-3x mb-4 opacity-50"></i>
                                <p>No hay variantes disponibles</p>
                            </td>
                        </tr>
                    @endforelse
                </x-slot:tbody>
            </x-table>
        </div>
    </div>
@endsection