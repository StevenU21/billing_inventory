@extends('layouts.app')
@section('title', 'Registrar Movimiento - Sesión #' . $session->id)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Caja Registradora', 'href' => route('admin.cash-register.index')],
            ['label' => 'Sesión #' . $session->id, 'href' => route('admin.cash-register.show', $session)],
            ['label' => 'Registrar Movimiento'],
        ]" />

        <x-page-header title="Registrar Movimiento"
            subtitle="Sesión de caja {{ $session->ref }} - {{ $session->user?->short_name ?? '-' }}">
            <x-link href="{{ route('admin.cash-register.index') }}" variant="secondary" icon="fas fa-arrow-left">
                Volver
            </x-link>
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-form action="{{ route('admin.cash-register.movements.store', $session) }}" method="POST">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-inputs.select name="type" label="Tipo de Movimiento" :options="[
            'deposit' => 'Depósito (+)',
            'withdrawal' => 'Retiro (-)',
            'adjustment_in' => 'Ajuste Entrada (+)',
            'adjustment_out' => 'Ajuste Salida (-)',
        ]" :selected="old('type')" placeholder="Seleccionar..." required />
                    @error('type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    <x-inputs.text name="amount" label="Monto" type="number" :value="old('amount')" placeholder="0.00"
                        step="0.01" min="0" required />
                    @error('amount')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @php
                    $paymentMethodOptions = $paymentMethods->pluck('name', 'id')->toArray();
                    $defaultPaymentMethod = old('payment_method_id') ?? $paymentMethods->firstWhere('is_cash', true)?->id;
                @endphp

                <x-inputs.select name="payment_method_id" label="Método de Pago" :options="$paymentMethodOptions"
                    :selected="$defaultPaymentMethod" required />
                @error('payment_method_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <x-inputs.textarea name="description" label="Descripción" :value="old('description')"
                    placeholder="Motivo del movimiento..." rows="3" />
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                @error('movement')
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    </div>
                @enderror

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-link href="{{ route('admin.cash-register.index') }}" variant="secondary" icon="fas fa-times">
                        Cancelar
                    </x-link>
                    <x-inputs.button type="submit" icon="fas fa-save">
                        Registrar Movimiento
                    </x-inputs.button>
                </div>
            </div>
        </x-form>
    </div>
@endsection