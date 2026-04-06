<?php

namespace App\Reports;

use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use App\Models\Company;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SingleQuotationReport implements ReportDefinition
{
    private array $relations;

    public function __construct(protected Quotation $quotation)
    {
    }

    public function query(Request $request)
    {
        // For single report query, we return a query builder for the specific ID
        // The complexity is mainly in viewData processing, so this query is simple.
        return Quotation::where('id', $this->quotation->id)
            ->with(['QuotationDetails.productVariant.product.tax', 'user', 'client']);
    }

    public function view(): string
    {
        return 'cashier.quotations.proforma';
    }

    public function filename(): string
    {
        return 'proforma_cotizacion_#' . $this->quotation->id . '_' . now()->format('Ymd_His') . '.pdf';
    }

    public function shouldValidateLimit(): bool
    {
        return false;
    }

    public function viewData(Collection $data): array
    {
        // $data contains the hydrated quotation models (collection of 1)
        $quotation = $data->first();
        if (!$quotation) {
            return [];
        }

        $company = Company::first();
        $details = [];

        foreach ($quotation->QuotationDetails as $qd) {
            $variant = $qd->productVariant;
            $product = $variant?->product;
            $tax = $product?->tax;

            $unitNetPrice = $qd->unit_price?->getAmount()->toFloat() ?? 0.0;
            $taxPercentage = $tax ? (float) $tax->percentage : null;
            $discountAmount = $qd->discount && $qd->discount_amount
                ? $qd->discount_amount->getAmount()->toFloat()
                : 0.0;
            $qty = (int) $qd->quantity;

            $lineBase = max(0, ($unitNetPrice * $qty) - $discountAmount);
            $lineTax = round($lineBase * (($taxPercentage ?? 0) / 100), 2);
            $lineSubtotal = round($lineBase + $lineTax, 2);
            $unitTaxAmount = round($unitNetPrice * (($taxPercentage ?? 0) / 100), 2);

            $details[] = [
                'variant' => $variant,
                'inventory' => null,
                'quantity' => $qty,
                'unit_price' => $unitNetPrice,
                'sub_total' => $lineSubtotal,
                'discount' => (bool) $qd->discount,
                'discount_amount' => $discountAmount,
                'unit_tax_amount' => $unitTaxAmount,
                'tax_percentage' => $taxPercentage,
                'line_tax' => $lineTax,
            ];
        }

        $total = array_sum(array_column($details, 'sub_total'));
        $totalTax = array_sum(array_column($details, 'line_tax'));

        $totals = [
            'sub_total' => max(0, $total - $totalTax),
            'total' => $total,
            'totalTax' => $totalTax,
        ];

        $validityDays = $quotation->valid_until ? $quotation->created_at->diffInDays($quotation->valid_until) : 15;

        return [
            'company' => $company,
            'entity' => $quotation->client,
            'details' => $details,
            'totals' => $totals,
            'quotation_date' => optional($quotation->created_at)?->toDateString(),
            'user' => $quotation->user,
            'quotation' => $quotation,
            'validityDays' => $validityDays,
        ];
    }
}
