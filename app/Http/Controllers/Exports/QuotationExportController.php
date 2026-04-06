<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Reports\SingleQuotationReport;
use Deifhelt\LaravelReports\Preview\PreviewWindowReportManager;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuotationExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PreviewWindowReportManager $previews)
    {
    }

    public function show(Quotation $quotation, Request $request)
    {
        $this->authorize('view', $quotation);

        return $this->previews->process(
            report: new SingleQuotationReport($quotation),
            request: $request,
            title: 'Proforma #' . $quotation->id,
            route: 'quotations.export.show',
        );
    }
}
