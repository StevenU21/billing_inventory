<?php

namespace App\Reports;

use App\Models\Inventory;
use Deifhelt\LaravelReports\Interfaces\ReportDefinition;
use Deifhelt\LaravelReports\Traits\DefaultReportConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReport implements ReportDefinition
{
    use DefaultReportConfiguration;

    public function query(Request $request)
    {
        $categoryId = $request->input('category_id');
        $lowStockOnly = $request->boolean('low_stock');

        $query = Inventory::query()
            ->select([
                'inventories.id',
                'inventories.stock',
                'inventories.average_cost',
                'inventories.min_stock',
                'products.name as product_name',
                'products.code as product_code',
                'product_variants.sku',
                DB::raw('(inventories.stock * inventories.average_cost) as total_value'),
            ])
            ->join('product_variants', 'inventories.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id');

        if ($categoryId) {
            $query->join('brands', 'products.brand_id', '=', 'brands.id')
                ->where('brands.category_id', $categoryId);
        }

        if ($lowStockOnly) {
            $query->whereRaw('inventories.stock <= inventories.min_stock');
        }

        return $query;
    }

    public function view(): string
    {
        return 'admin.reports.inventory';
    }

    public function filename(): string
    {
        return 'reporte_inventario_' . now()->format('Ymd_His') . '.pdf';
    }
}
