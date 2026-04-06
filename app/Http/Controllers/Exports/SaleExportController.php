<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;

use App\Reports\SaleReceiptReport;
use Deifhelt\LaravelReports\Preview\PreviewWindowReportManager;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Reports\SingleSalesReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SaleExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PreviewWindowReportManager $previews)
    {
    }

    public function show(Sale $sale, Request $request)
    {
        $this->authorize('export', $sale);

        return $this->previews->process(
            report: new SingleSalesReport($sale),
            request: $request,
            title: 'Venta #' . $sale->id,
            route: 'sales.export.show',
        );
    }

    public function receipt(Sale $sale, Request $request)
    {
        $this->authorize('export', $sale);

        return $this->previews->process(
            report: new SaleReceiptReport($sale),
            request: $request,
            title: 'Recibo #' . $sale->id,
            route: 'sales.export.receipt',
        );
    }
}
