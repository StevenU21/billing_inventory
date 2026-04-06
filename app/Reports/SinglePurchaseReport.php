<?php

namespace App\Reports;

use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use App\Models\Company;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SinglePurchaseReport implements ReportDefinition
{
    public function __construct(protected Purchase $purchase)
    {
    }

    public function query(Request $request)
    {
        return Purchase::where('id', $this->purchase->id)
            ->with([
                'entity',
                'user',
                'paymentMethod',
                'details.productVariant.product',
                'details.productVariant.attributeValues.attribute',
            ]);
    }

    public function view(): string
    {
        return 'admin.purchases.report';
    }

    public function filename(): string
    {
        return 'compra_' . $this->purchase->id . '.pdf';
    }

    public function shouldValidateLimit(): bool
    {
        return false;
    }

    public function viewData(Collection $data): array
    {
        $purchase = $data->first();

        return [
            'singlePurchase' => $purchase,
            'company' => Company::first(),
            'reportTitle' => __('Reporte de Compra #:id', ['id' => $purchase->id]),
            'data' => $data, // Generic key required by manager? Manager passes 'data' automatically.
            // But view might expect 'singlePurchase'.
        ];
    }
}
