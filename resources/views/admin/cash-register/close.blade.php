@extends('layouts.app')
@section('title', 'Cerrar Caja')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Caja Registradora', 'href' => route('admin.cash-register.index')],
            ['label' => 'Sesión #' . $session->id, 'href' => route('admin.cash-register.show', $session)],
            ['label' => 'Cerrar Caja'],
        ]" />

        <x-page-header title="Cerrar Caja" subtitle="Finalizar sesión {{ $session->ref }}.">
            <x-link href="{{ route('admin.cash-register.index') }}" variant="secondary" icon="fas fa-arrow-left">
                Volver
            </x-link>
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-form action="{{ route('admin.cash-register.close', $session) }}" method="POST">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6" x-data="{
                                    denominations: [
                                        { label: 'C$ 1,000', value: 1000, count: '' },
                                        { label: 'C$ 500', value: 500, count: '' },
                                        { label: 'C$ 200', value: 200, count: '' },
                                        { label: 'C$ 100', value: 100, count: '' },
                                        { label: 'C$ 50', value: 50, count: '' },
                                        { label: 'C$ 20', value: 20, count: '' },
                                        { label: 'C$ 10', value: 10, count: '' },
                                        { label: 'C$ 5', value: 5, count: '' },
                                        { label: 'C$ 1', value: 1, count: '' },
                                        { label: 'C$ 0.50', value: 0.50, count: '' },
                                        { label: 'C$ 0.25', value: 0.25, count: '' },
                                    ],
                                    expectedBalance: {{ $session->expected_closing_balance->getMinorAmount()->toInt() / 100 }},
                                    get totalCash() {
                                        const sum = this.denominations.reduce((sum, item) => {
                                            const val = parseFloat(item.value);
                                            const qty = parseInt(item.count) || 0;
                                            return sum + (val * qty);
                                        }, 0);
                                        return parseFloat(sum.toFixed(2));
                                    },
                                    get difference() {
                                        return parseFloat((this.totalCash - this.expectedBalance).toFixed(2));
                                    },
                                    get hasDifference() {
                                        return Math.abs(this.difference) > 0.01;
                                    },
                                    get differenceFormatted() {
                                        return new Intl.NumberFormat('es-NI', { 
                                            style: 'currency', 
                                            currency: 'NIO',
                                            minimumFractionDigits: 2 
                                        }).format(Math.abs(this.difference));
                                    },
                                    get differenceType() {
                                        if (this.difference > 0) return 'Sobrante';
                                        return 'Cuadre Perfecto';
                                    },
                                    get feedbackColor() {
                                        if (this.difference > 0) return 'text-yellow-600 dark:text-yellow-400 border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20';
                                        return 'text-green-600 dark:text-green-400 border-green-200 bg-green-50 dark:bg-green-900/20';
                                    },
                                    showDetail: true
                                }">

                <div class="mb-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="fas fa-calculator text-indigo-600 dark:text-indigo-400 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Arqueo de Efectivo</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Ingrese la cantidad física de cada denominación.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showDetail = !showDetail"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                        <span x-text="showDetail ? 'Ocultar Denominaciones' : 'Mostrar Denominaciones'"></span>
                    </button>
                </div>

                {{-- Arqueo Calculator Grid --}}
                <div x-show="showDetail" x-transition class="mb-8">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <template x-for="(item, index) in denominations" :key="index">
                            <div
                                class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                <div class="flex justify-between items-center mb-2">
                                    <label :for="'denom_' + index"
                                        class="block text-xs font-bold text-gray-600 dark:text-gray-300"
                                        x-text="item.label"></label>
                                    <i class="fas fa-money-bill-wave text-gray-300 dark:text-gray-600 text-xs"></i>
                                </div>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" :id="'denom_' + index" x-model="item.count" min="0" step="1"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 text-center font-mono font-bold"
                                        placeholder="0">
                                </div>
                                <div class="mt-2 text-right border-t border-gray-200 dark:border-gray-600 pt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Total:</span>
                                    <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 block"
                                        x-text="new Intl.NumberFormat('es-NI', { style: 'currency', currency: 'NIO' }).format((item.value * (item.count || 0)))">
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    {{-- Total Input with Custom Styling --}}
                    <div>
                        <label for="actual_closing_balance"
                            class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">
                            Total Efectivo Contado
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold">C$</span>
                            </div>
                            <input type="number" name="actual_closing_balance" id="actual_closing_balance" step="0.01"
                                min="0" required readonly x-model="totalCash"
                                class="block w-full pl-10 pr-12 mt-1 text-xl font-bold bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg border border-gray-300 dark:border-gray-600 focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 h-[42px]" />
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-medium text-sm">NIO</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            Calculado automáticamente del arqueo.
                        </p>
                    </div>
                </div>

                <x-inputs.textarea
                    name="notes"
                    label="Notas de Cierre"
                    :value="old('notes')"
                    placeholder="Escriba aquí cualquier observación, justificación de sobrantes..."
                    rows="3"
                    x-bind:required="hasDifference"
                />
                <p x-show="hasDifference" x-transition class="mt-1 text-xs text-red-500 font-semibold">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Requiere justificación
                </p>

                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <x-link href="{{ route('admin.cash-register.show', $session) }}" variant="secondary">
                        Cancelar
                    </x-link>
                    <x-inputs.button type="submit" icon="fas fa-check-double">
                        Verificar y Cerrar Caja
                    </x-inputs.button>
                </div>
            </div>
        </x-form>
    </div>
@endsection