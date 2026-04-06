<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Reports\SinglePurchaseReport;
use Deifhelt\LaravelReports\Preview\PreviewWindowReportManager;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PreviewWindowReportManager $previews)
    {
    }

    public function show(Purchase $purchase, Request $request)
    {
        $this->authorize('viewAny', Purchase::class);

        return $this->previews->process(
            report: new SinglePurchaseReport($purchase),
            request: $request,
            title: 'Compra #' . $purchase->id,
            route: 'purchases.export.show',
        );
    }
}
