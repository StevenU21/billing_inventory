<?php

namespace App\Reports;

use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use App\Models\Company;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SingleSalesReport implements ReportDefinition
{
    public function __construct(protected Sale $sale)
    {
    }

    public function query(Request $request): Builder
    {
        return Sale::query()
            ->where('id', $this->sale->id)
            ->with([
                'client',
                'user',
                'paymentMethod',
                'saleDetails.productVariant.product.brand',
                'saleDetails.productVariant.product.tax'
            ]);
    }

    public function view(): string
    {
        return 'admin.sales.report';
    }

    public function filename(): string
    {
        return 'Venta_' . $this->sale->id . '.pdf';
    }

    public function shouldValidateLimit(): bool
    {
        return false;
    }

    public function viewData(Collection $data): array
    {
        $sale = $data->first();

        return [
            'singleSale' => $sale,
            'company' => Company::first(),
            'reportTitle' => 'Reporte de Venta #' . $this->sale->id,
            'totals' => [
                'subtotal_formatted' => $sale->formatted_sub_total,
                'discount_formatted' => $sale->formatted_discount_total,
                'tax_formatted' => $sale->formatted_tax_amount,
                'total_formatted' => $sale->formatted_total,
            ]
        ];
    }
}
