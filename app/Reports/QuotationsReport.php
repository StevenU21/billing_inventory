<?php

namespace App\Reports;

use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use App\Models\Company;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QuotationsReport implements ReportDefinition
{
    public function shouldValidateLimit(): bool
    {
        return true;
    }

    public function query(Request $request)
    {
        return QueryBuilder::for(Quotation::class)
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::exact('entity_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('from'),
                AllowedFilter::scope('to'),
            ])
            ->allowedSorts(['id', 'created_at', 'total', 'valid_until', 'status'])
            ->defaultSort('-created_at')
            ->orderByDesc('id')
            ->with(['client', 'user', 'QuotationDetails.productVariant.product']);
    }

    public function view(): string
    {
        return 'admin.quotations.report_list';
    }

    public function filename(): string
    {
        return 'cotizaciones_' . now()->format('Ymd_His') . '.pdf';
    }

    public function summary(Collection $data): array
    {
        return [
            'records' => $data->count(),
            'total' => $data->reduce(fn($carry, $item) => $carry + ($item->total?->getAmount()->toFloat() ?? 0), 0),
        ];
    }

    public function extraData(Request $request): array
    {
        return [
            'reportTitle' => __('Reporte de Cotizaciones'),
            'company' => Company::first(),
            'filters' => array_filter(is_array($f = $request->input('filter')) ? $f : [], fn($value) => $value !== null && $value !== '')
        ];
    }

    public function viewData(Collection $data): array
    {
        return [
            'quotations' => $data,
            'totals' => $this->summary($data),
            'reportTitle' => __('Reporte de Cotizaciones'),
        ];
    }
}
