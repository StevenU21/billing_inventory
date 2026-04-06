@extends('layouts.app')
@section('title', 'Nuevo Inventario')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumb :items="[
            ['label' => 'Modulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Inventarios', 'href' => route('inventories.index')],
            ['label' => 'Nuevo Inventario'],
        ]" />

        <x-page-header title="Nuevo Inventario" subtitle="Registra existencias iniciales por variante de producto."
            icon="fa-plus-circle">
            <x-page-header.link href="{{ route('inventories.index') }}" icon="fas fa-arrow-left">
                Volver al listado
            </x-page-header.link>
        </x-page-header>

        <form action="{{ route('inventories.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Card: Información Principal --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Datos del Inventario</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Variant Selection --}}
                    <div class="md:col-span-2">
                        <x-inputs.select name="product_variant_id" label="Producto / Variante" :options="$variants" required
                            placeholder="Seleccione una variante..." />
                    </div>

                    {{-- Currency Selection --}}
                    <div>
                        <x-inputs.select name="currency" label="Moneda" :options="array_combine($currencies, $currencies)"
                            required />
                    </div>
                </div>
            </div>

            {{-- Card: Control de Stock --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Existencias</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Min Stock --}}
                    <x-inputs.text name="min_stock" label="Stock Mínimo" type="number" step="0.0001" min="0"
                        placeholder="0.00" required />

                    {{-- Initial Stock (Optional but recommended) --}}
                    <div>
                        <x-inputs.text name="stock" label="Stock Inicial (Opcional)" type="number" step="0.0001" min="0"
                            placeholder="0.00" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si ingresa un valor mayor a 0, se creará un
                            movimiento de ajuste inicial.</p>
                    </div>
                </div>
            </div>

            {{-- Card: Costos Iniciales --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Costos</h3>
                <div class="grid grid-cols-1 gap-4">
                    {{-- Unit Price for Initial Stock --}}
                    <div>
                        <x-inputs.text name="unit_price" label="Costo Unitario Inicial (Opcional)" type="number"
                            step="0.0001" min="0" placeholder="0.00" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Utilizado para valorar el stock inicial si
                            se define.</p>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="flex justify-end gap-4 pt-4">
                <x-inputs.button type="button" variant="secondary"
                    onclick="window.location='{{ route('inventories.index') }}'">
                    Cancelar
                </x-inputs.button>
                <x-inputs.button type="submit" variant="primary">
                    <i class="fas fa-save mr-2"></i> Guardar Inventario
                </x-inputs.button>
            </div>
        </form>
    </div>
@endsection