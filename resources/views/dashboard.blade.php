@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'icon' => 'fa-home'],
        ]" />

        <x-page-header title="Dashboard" icon="fa-tachometer-alt" subtitle="Resumen de negocio">
        </x-page-header>

        <!-- KPI Cards -->
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <!-- Ventas Hoy -->
            <div class="flex items-start justify-between p-5 bg-white rounded-xl shadow-sm dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50">
                <div>
                    <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Ventas de Hoy</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $sales_today ?? '0.00' }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">+ {{ $sales_today_tax ?? '0.00' }} de imp. (Total)</p>
                </div>
                <div class="p-3 text-purple-500 bg-purple-100 rounded-lg dark:text-purple-100 dark:bg-purple-500">
                    <i class="fas fa-calendar-day fa-lg"></i>
                </div>
            </div>

            <!-- Cuentas por Cobrar -->
            <div class="flex items-start justify-between p-5 bg-white rounded-xl shadow-sm dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50">
                <div>
                    <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Cuentas por Cobrar</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $accounts_receivable ?? '0.00' }}</p>
                </div>
                <div class="p-3 text-orange-500 bg-orange-100 rounded-lg dark:text-orange-100 dark:bg-orange-500">
                    <i class="fas fa-hand-holding-usd fa-lg"></i>
                </div>
            </div>

            <!-- Ventas del Mes -->
            <div class="flex items-start justify-between p-5 bg-white rounded-xl shadow-sm dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50">
                <div>
                    <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Ventas del Mes</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $sales_month ?? '0.00' }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">+ {{ $sales_month_tax ?? '0.00' }} de imp. (Total)</p>
                </div>
                <div class="p-3 text-blue-500 bg-blue-100 rounded-lg dark:text-blue-100 dark:bg-blue-500">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                </div>
            </div>

            <!-- Ganancia Bruta Estimada -->
            <div class="flex items-start justify-between p-5 bg-white rounded-xl shadow-sm dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50">
                <div>
                    <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Ganancia Bruta Estimada</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $profit_month ?? '0.00' }}</p>
                </div>
                <div class="p-3 text-green-500 bg-green-100 rounded-lg dark:text-green-100 dark:bg-green-500">
                    <i class="fas fa-chart-pie fa-lg"></i>
                </div>
            </div>
        </div>

        <div class="grid gap-6 mb-8 md:grid-cols-2">
            <!-- Chart -->
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                    Ventas (Últimos 7 días)
                </h4>
                <canvas id="salesChart"></canvas>
            </div>

            <!-- Low Stock Table -->
            <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                    Productos en Riesgo (Bajo Stock)
                </h4>
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3">Stock Actual</th>
                                <th class="px-4 py-3">Mínimo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @forelse($low_stock_products ?? [] as $inventory)
                                <tr class="text-gray-700 dark:text-gray-400">
                                    <td class="px-4 py-3 text-sm font-medium">
                                        {{ $inventory->audit_display }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-red-600 dark:text-red-400">
                                        {{ $inventory->formatted_stock }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $inventory->formatted_min_stock }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">
                                        No hay productos con bajo stock.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            const chartData = @json($chart_data ?? ['labels' => [], 'data' => []]);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Ventas (C$)',
                        data: chartData.data,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endpush