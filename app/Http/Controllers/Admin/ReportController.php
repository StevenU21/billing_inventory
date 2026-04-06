<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reports\FinancialReport;
use App\Reports\InventoryReport;
use App\Reports\SalesReport;
use Deifhelt\LaravelReports\Preview\PreviewWindowReportManager;
use Illuminate\Http\Request;

/**
 * ReportController
 * 
 * Handles PDF/Excel exports for reports.
 * No filter pages - exports are triggered directly from contextual views.
 */
class ReportController extends Controller
{
    public function __construct(
        protected PreviewWindowReportManager $previews
    ) {
    }

    public function exportSales(Request $request)
    {
        return $this->previews->process(
            report: new SalesReport(),
            request: $request,
            title: 'Reporte de Ventas',
            route: 'reports.sales.export',
        );
    }

    public function exportInventory(Request $request)
    {
        return $this->previews->process(
            report: new InventoryReport(),
            request: $request,
            title: 'Reporte de Inventario',
            route: 'reports.inventory.export',
        );
    }

    public function exportFinancial(Request $request)
    {
        return $this->previews->process(
            report: new FinancialReport(),
            request: $request,
            title: 'Reporte Financiero',
            route: 'reports.financial.export',
        );
    }
}

