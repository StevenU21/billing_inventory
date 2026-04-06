<?php

namespace App\Reports;

use App\Models\Sale;
use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use Deifhelt\LaravelReports\Traits\DefaultReportConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReport implements ReportDefinition
{
    use DefaultReportConfiguration;

    public function query(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        // Financial report often needs to show daily totals or just a big list
        // Let's do a daily aggregate for the PDF/table, or a list of transactions if filtered tight.
        // For report definition, usually detailed is safer, but "Financial > Taxes / Net Profit" implies aggregation.
        // However, standard PDF reports usually iterate rows.
        // Let's just return the Sales with strict financial columns for now.

        return Sale::query()
            ->select([
                'sales.id',
                'sales.sale_date',
                'sales.total',
                'sales.tax_amount',
                'sales.sub_total', // Net
                DB::raw('(sales.total - sales.tax_amount) as net_amount'), // Double check logic
                'sales.status',
                'sales.is_credit',
            ])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->orderBy('sale_date');
    }

    public function view(): string
    {
        return 'admin.reports.financial';
    }

    public function filename(): string
    {
        return 'reporte_financiero_' . now()->format('Ymd_His') . '.pdf';
    }
}
