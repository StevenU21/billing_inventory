@extends('layouts.cashier')
@section('title', 'Punto de Venta')

@section('content')
    <div class="h-full min-h-0 flex flex-col" x-data="pos()">
        <!-- Page header card -->
        <style>
            .animate-gradient {
                background-image: linear-gradient(90deg, #c026d3, #7c3aed, #4f46e5, #c026d3);
                background-size: 300% 100%;
                animation: gradientShift 8s linear infinite alternate;
                filter: saturate(1.2) contrast(1.05);
                will-change: background-position;
            }

            @keyframes gradientShift {
                0% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .animate-gradient {
                    animation: none;
                }
            }
        </style>
        <section
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 shadow-lg animate-gradient mx-4 sm:mx-6 lg:mx-8">
            <div class="absolute inset-0 opacity-20 pointer-events-none"
                style="background-image: radial-gradient(ellipse at top left, rgba(255,255,255,.35), transparent 40%), radial-gradient(ellipse at bottom right, rgba(0,0,0,.25), transparent 40%);">
            </div>
            <div class="relative p-4 md:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl md:text-3xl font-extrabold text-white tracking-tight flex items-center">
                            <i class="fas fa-cash-register text-white/90 mr-3"></i>
                            Punto de Venta
                        </h1>
                        <p class="mt-1 text-white/80 text-xs md:text-sm">Registra ventas y pagos de forma ágil y consistente.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 text-white text-sm">
                            <i class="fas fa-keyboard"></i>
                            Atajos: <strong class="ml-1">F3</strong> Buscar • <strong>F10</strong> Pagar
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex-1 min-h-0 overflow-hidden">
            <x-session-message />

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-4 h-full min-h-0">
                <!-- Main Content -->
                <div
                    class="lg:col-span-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 flex flex-col rounded-xl p-3 md:p-4 h-full min-h-0 shadow">
                    <!-- Cart Items -->
                    <div class="flex-1 min-h-0 flex items-center justify-center">
                        <div class="text-center px-4">
                            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-800 dark:text-gray-100">
                                No hay artículos</h2>
                            <p class="mt-1 text-sm sm:text-base text-gray-600 dark:text-gray-300">Agregue productos a la
                                orden usando código de
                                barras, código o búsqueda
                                <span class="inline-block font-semibold text-purple-300 dark:text-purple-300">(F3)</span>
                            </p>
                        </div>
                    </div>
                    <!-- Totals -->
                    <div class="mt-3 border-t border-gray-200 dark:border-gray-700 pt-4 text-base sm:text-lg">
                        <div class="flex justify-between mb-2 tabular-nums">
                            <span class="text-gray-600 dark:text-gray-300">Subtotal</span>
                            <span class="font-semibold">C$ 150.00</span>
                        </div>
                        <div class="flex justify-between mb-2 tabular-nums">
                            <span class="text-gray-600 dark:text-gray-300">Impuestos</span>
                            <span class="font-semibold">C$ 0</span>
                        </div>
                        <div class="flex justify-between font-extrabold text-2xl tabular-nums">
                            <span>Total</span>
                            <span>C$ 0</span>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <aside
                    class="lg:col-span-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-xl p-3 md:p-4 h-full min-h-0 flex flex-col justify-between shadow">
                    <div>
                        <h3
                            class="text-lg sm:text-xl font-semibold mb-4 flex items-center gap-2 text-gray-800 dark:text-gray-100">
                            <i class="fas fa-wallet text-purple-400"></i>
                            Seleccione método de pago
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 mb-4">
                            <button type="button"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 transition text-sm sm:text-base">
                                <i class="fas fa-money-bill-wave text-purple-400"></i>
                                <span class="font-medium">Depósito</span>
                            </button>
                            <button type="button"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 transition text-sm sm:text-base">
                                <i class="fas fa-dollar-sign text-purple-400"></i>
                                <span class="font-medium">Efectivo</span>
                            </button>
                            <button type="button"
                                class="sm:col-span-2 inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900 transition text-sm sm:text-base">
                                <i class="fas fa-credit-card text-purple-400"></i>
                                <span class="font-medium">Tarjeta</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <button type="button"
                            class="p-3 sm:p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F6</div>
                            <i class="fas fa-search mt-1 text-base sm:text-lg"></i>
                            <div class="text-[10px] sm:text-xs">Buscar</div>
                        </button>
                        <button type="button"
                            class="p-3 sm:p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition col-span-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F7</div>
                            <div class="text-[10px] sm:text-xs">Cuentas por cobrar</div>
                        </button>
                        <button type="button"
                            class="p-3 sm:p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F4</div>
                            <i class="fas fa-percentage mt-1 text-base sm:text-lg"></i>
                            <div class="text-[10px] sm:text-xs">Descuentos</div>
                        </button>
                        <button type="button"
                            class="p-3 sm:p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F5</div>
                            <i class="fas fa-user-tag mt-1 text-base sm:text-lg"></i>
                            <div class="text-[10px] sm:text-xs">Clientes</div>
                        </button>
                        <button type="button"
                            class="p-3 sm:p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F9</div>
                            <div class="text-[10px] sm:text-xs">Proforma</div>
                        </button>
                        <button type="button"
                            class="p-3 sm:p-4 bg-red-600 text-white rounded-lg text-center hover:bg-red-500 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F8</div>
                            <i class="fas fa-trash mt-1 text-base sm:text-lg"></i>
                            <div class="text-[10px] sm:text-xs">Anular Orden</div>
                        </button>
                        <button type="button"
                            class="col-span-3 sm:col-span-2 p-3 sm:p-4 bg-purple-600 text-white rounded-lg text-center hover:bg-purple-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-300 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-900">
                            <div class="text-xs sm:text-sm font-bold">F10</div>
                            <div class="text-[10px] sm:text-xs">Pago</div>
                        </button>
                    </div>
                </aside>
            </div>
        </div>

        <script>
            function pos() {
                return {
                    // Lógica del componente POS
                }
            }
        </script>
    @endsection
