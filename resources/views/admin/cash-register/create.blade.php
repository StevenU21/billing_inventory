@extends('layouts.app')
@section('title', 'Abrir Caja')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Caja Registradora', 'href' => route('admin.cash-register.index')],
            ['label' => 'Abrir Caja'],
        ]" />

        <x-page-header title="Abrir Caja" subtitle="Iniciar una nueva sesión de caja registradora." />

        <div class="mt-4">
            <x-session-message />
        </div>

        <div class="max-w-2xl mx-auto">
            <x-form action="{{ route('admin.cash-register.store') }}" method="POST">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <i class="fas fa-cash-register text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Nueva Sesión de Caja</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Fecha: {{ now()->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="opening_balance"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Monto de Apertura <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="opening_balance" id="opening_balance" step="0.01" min="0"
                                placeholder="0.00" value="{{ old('opening_balance', '0') }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-purple-500 focus:border-purple-500" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Ingrese el efectivo inicial disponible en la caja.
                            </p>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Notas (Opcional)
                            </label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Observaciones adicionales..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-purple-500 focus:border-purple-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <x-link href="{{ route('admin.cash-register.index') }}" variant="secondary">
                            Cancelar
                        </x-link>
                        <x-inputs.button type="submit" icon="fas fa-door-open">
                            Abrir Caja
                        </x-inputs.button>
                    </div>
                </div>
            </x-form>
        </div>
    </div>
@endsection