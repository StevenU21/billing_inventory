@extends('layouts.app')
@section('title', 'Editar Inventario')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Inventario', 'href' => route('inventories.index')],
            ['label' => 'Editar #' . $inventory->id],
        ]" />

        <x-page-header title="Movimientos de Inventario"
            subtitle="{{ $inventory->productVariant->product->name }} - {{ $inventory->productVariant->sku }} | Stock Actual: {{ $inventory->stock }}"
            icon="fa-exchange-alt">
            <x-link href="{{ route('inventories.index') }}" variant="secondary" icon="fas fa-arrow-left">
                Volver
            </x-link>
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <form action="{{ route('inventories.update', $inventory) }}" method="POST" x-data="{
                    movementType: '',
                    get showQuantity() { return this.movementType !== ''; },
                    get isAdjustment() { return ['{{ \App\Enums\InventoryMovementType::AdjustmentIn->value }}', '{{ \App\Enums\InventoryMovementType::AdjustmentOut->value }}'].includes(this.movementType); },
                    get isAdjustmentIn() { return this.movementType === '{{ \App\Enums\InventoryMovementType::AdjustmentIn->value }}'; }
                }">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 space-y-6">
                {{-- Sección Principal: Stock Mínimo y Tipo de Movimiento --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-inputs.text name="min_stock" label="Stock Mínimo" type="number" step="0.0001" min="0"
                            :value="old('min_stock', $inventory->min_stock)" required />
                    </div>

                    <div>
                        <x-inputs.select name="movement_type" label="Tipo de Movimiento" :options="$movementTypes"
                            placeholder="Sin movimiento (Solo actualizar detalles)" x-model="movementType" />
                    </div>
                </div>

                {{-- Campos Dinámicos del Movimiento --}}
                <template x-if="showQuantity">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <div>
                            <x-inputs.text name="quantity" label="Cantidad" type="number" step="0.0001" min="0.0001"
                                placeholder="0.00" required />
                        </div>

                        <template x-if="isAdjustment">
                            <div>
                                <x-inputs.select name="adjustment_reason" label="Motivo del Ajuste"
                                    :options="$adjustmentReasons" placeholder="Seleccione un motivo..." required />
                            </div>
                        </template>

                        <template x-if="isAdjustmentIn">
                            <div>
                                <x-inputs.text name="unit_price" label="Costo Unitario (Opcional)" type="number"
                                    step="0.0001" min="0" placeholder="0.00" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Deja vacío para usar costo promedio.
                                </p>
                            </div>
                        </template>

                        <div class="md:col-span-2">
                            <x-inputs.textarea name="notes" label="Notas / Referencia" rows="3"
                                placeholder="Detalles adicionales del movimiento..." />
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-link href="{{ route('inventories.index') }}" variant="secondary" icon="fas fa-times">
                        Cancelar
                    </x-link>
                    <x-inputs.button type="submit" icon="fas fa-save">
                        Guardar Cambios
                    </x-inputs.button>
                </div>
            </div>
        </form>
    </div>
@endsection