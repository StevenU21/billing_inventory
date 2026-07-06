<?php

namespace App\Services;

use App\Enums\AccountReceivableStatus;
use App\Enums\SaleStatus;
use App\Models\AccountReceivable;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleDetail;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * DashboardService
 *
 * Service for calculating dashboard business KPIs.
 */
class DashboardService
{
    /**
     * Get business dashboard information.
     */
    public function getBusinessKPIs(): array
    {
        $defaultCurrency = 'NIO';
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        // 1. Ventas de Hoy
        $salesToday = Sale::where('sale_date', '>=', $today)
            ->where('status', '!=', SaleStatus::Cancelled)
            ->get();

        $salesTodayTotal = $salesToday->reduce(function ($carry, $sale) {
            return $carry->plus($sale->sub_total ?? Money::zero($carry->getCurrency()));
        }, Money::zero($defaultCurrency));

        $salesTodayTaxTotal = $salesToday->reduce(function ($carry, $sale) {
            return $carry->plus($sale->tax_amount ?? Money::zero($carry->getCurrency()));
        }, Money::zero($defaultCurrency));

        // 2. Ventas del Mes
        $salesMonth = Sale::where('sale_date', '>=', $startOfMonth)
            ->where('status', '!=', SaleStatus::Cancelled)
            ->get();

        $salesMonthTotal = $salesMonth->reduce(function ($carry, $sale) {
            return $carry->plus($sale->sub_total ?? Money::zero($carry->getCurrency()));
        }, Money::zero($defaultCurrency));

        $salesMonthTaxTotal = $salesMonth->reduce(function ($carry, $sale) {
            return $carry->plus($sale->tax_amount ?? Money::zero($carry->getCurrency()));
        }, Money::zero($defaultCurrency));

        // 3. Ganancia Bruta Estimada (Excluyendo impuestos)
        $salesMonthIds = $salesMonth->pluck('id');
        $salesDetailsMonth = SaleDetail::whereIn('sale_id', $salesMonthIds)->get();

        $profitMonthTotal = $salesDetailsMonth->reduce(function ($carry, $detail) {
            $subTotal = $detail->sub_total ?? ($detail->unit_price ?? Money::zero($carry->getCurrency()))->multipliedBy($detail->quantity, RoundingMode::HALF_UP);
            $unitCost = $detail->unit_cost ?? Money::zero($carry->getCurrency());
            $costTotal = $unitCost->multipliedBy($detail->quantity, RoundingMode::HALF_UP);
            $discount = $detail->discount_amount ?? Money::zero($carry->getCurrency());

            // Ganancia = Subtotal - Descuento - Costo Total
            $profit = $subTotal->minus($discount)->minus($costTotal);

            return $carry->plus($profit);
        }, Money::zero($defaultCurrency));

        // 4. Cuentas por Cobrar Pendientes
        $accountsReceivable = AccountReceivable::whereIn('status', [
            AccountReceivableStatus::Pending,
            AccountReceivableStatus::PartiallyPaid,
        ])->get();

        $accountsReceivableTotal = $accountsReceivable->reduce(function ($carry, $ar) {
            return $carry->plus($ar->balance ?? Money::zero($carry->getCurrency()));
        }, Money::zero($defaultCurrency));

        // 5. Gráfico de Ventas (Últimos 7 días)
        $chartData = $this->getSalesChartData($defaultCurrency);

        // 6. Tabla de Bajo Stock
        $lowStockProducts = Inventory::with(['productVariant.product'])
            ->whereColumn('stock', '<=', 'min_stock')
            ->limit(10)
            ->get();

        return [
            'sales_today' => $salesTodayTotal->formatTo('es_NI'),
            'sales_today_tax' => $salesTodayTaxTotal->formatTo('es_NI'),
            'sales_month' => $salesMonthTotal->formatTo('es_NI'),
            'sales_month_tax' => $salesMonthTaxTotal->formatTo('es_NI'),
            'profit_month' => $profitMonthTotal->formatTo('es_NI'),
            'accounts_receivable' => $accountsReceivableTotal->formatTo('es_NI'),
            'chart_data' => $chartData,
            'low_stock_products' => $lowStockProducts,
        ];
    }

    /**
     * Data for the last 7 days sales chart.
     */
    private function getSalesChartData(string $defaultCurrency): array
    {
        $labels = [];
        $data = [];

        // Generate last 7 days including today
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = ucfirst($date->isoFormat('dddd D')); // e.g., "Lunes 5"

            $salesOfDay = Sale::whereDate('sale_date', $date->toDateString())
                ->where('status', '!=', SaleStatus::Cancelled)
                ->get();

            $totalOfDay = $salesOfDay->reduce(function ($carry, $sale) {
                return $carry->plus($sale->sub_total ?? Money::zero($carry->getCurrency()));
            }, Money::zero($defaultCurrency));

            $data[] = $totalOfDay->getAmount()->toFloat();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
