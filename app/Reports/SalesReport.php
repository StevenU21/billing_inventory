<?php

namespace App\Reports;

use App\Models\Sale;
use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use Deifhelt\LaravelReports\Traits\DefaultReportConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReport implements ReportDefinition
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

        $status = $request->input('status', 'completed');
        $categoryId = $request->input('category_id');

        $query = Sale::query()
            ->select([
                'sales.id',
                'sales.is_credit',
                'sales.sale_date',
                'sales.total',
                'sales.status',
                DB::raw("(users.first_name || ' ' || users.last_name) as cashier_name"),
                DB::raw("(entities.first_name || ' ' || entities.last_name) as client_name"),
            ])
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('entities', 'sales.client_id', '=', 'entities.id')
            ->when($status !== 'all', fn($q) => $q->where('sales.status', $status))
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->orderBy('sales.sale_date', 'desc');

        // If filtering by category or showing product details, we need to join details
        if ($categoryId || $request->has('show_details')) {
            $query->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->join('product_variants', 'sale_details.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->addSelect([
                    'products.name as product_name',
                    'sale_details.quantity',
                    'sale_details.unit_price',
                    'sale_details.sub_total as row_total'
                ]);

            if ($categoryId) {
                // products -> brands -> categories
                $query->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->where('brands.category_id', $categoryId);
            }
        }

        return $query;
    }

    public function view(): string
    {
        return 'admin.reports.sales'; // Updated view path
    }

    public function filename(): string
    {
        return 'reporte_ventas_' . now()->format('Ymd_His') . '.pdf';
    }
}
