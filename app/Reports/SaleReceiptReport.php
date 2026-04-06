<?php

namespace App\Reports;

use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use App\Models\Company;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleReceiptReport implements ReportDefinition
{
    public function __construct(protected Sale $sale)
    {
    }

    public function query(Request $request)
    {
        return Sale::where('id', $this->sale->id);
    }

    public function view(): string
    {
        return 'cashier.sales.receipt';
    }

    public function filename(): string
    {
        return 'recibo_' . $this->sale->id . '.pdf';
    }

    public function shouldValidateLimit(): bool
    {
        return false;
    }

    public function paper(): array
    {
        $this->sale->loadMissing(['saleDetails']);
        $rows = $this->sale->saleDetails->count();

        $headerMm = 85;
        $footerMm = 65;
        $rowMm = 12;

        $totalMm = $headerMm + ($rows * $rowMm) + $footerMm + 10;

        $ptPerMm = 72 / 25.4;
        $widthPt = 80 * $ptPerMm;
        $heightPt = $totalMm * $ptPerMm;

        return [0, 0, $widthPt, $heightPt];
    }

    public function viewData(mixed $data): array
    {
        $this->sale->loadMissing(['saleDetails.productVariant.product.tax', 'user', 'client', 'paymentMethod', 'accountReceivable']);

        return [
            'sale' => $this->sale,
            'details' => $this->sale->saleDetails,
            'company' => Company::first(),
        ];
    }
}
