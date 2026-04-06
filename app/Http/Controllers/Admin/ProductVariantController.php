<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventorySearchResource;
use App\Http\Resources\ProductVariantAutocompleteResource;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Services\AutocompleteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductVariantController extends Controller
{
    use AuthorizesRequests;

    protected AutocompleteService $autocompleteService;

    public function __construct(AutocompleteService $autocompleteService)
    {
        $this->autocompleteService = $autocompleteService;
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', ProductVariant::class);

        $perPage = (int) $request->input('per_page', 5);
        $perPage = max(1, min(50, $perPage));

        $baseQuery = Inventory::query()
            ->with([
                'productVariant.product.tax',
                'productVariant.product.brand.category',
            ])
            ->whereHas('productVariant.product', function ($q2) {
                $q2->where('status', 'available');
            });

        // Apply Search Service for 'q'
        if ($q = $request->input('q')) {
            $this->autocompleteService->applySearch($baseQuery, $q, [
                'productVariant.product.name',
                'productVariant.product.code',
                'productVariant.sku',
                'productVariant.barcode',
            ]);
        }

        $query = QueryBuilder::for($baseQuery)
            ->allowedFilters(...[
                AllowedFilter::callback('category_id', fn ($q, $v) => $q->whereRelation('productVariant.product.brand', 'category_id', $v)),
                AllowedFilter::callback('brand_id', fn ($q, $v) => $q->whereRelation('productVariant.product', 'brand_id', $v)),
                AllowedFilter::callback('entity_id', fn ($q, $v) => $q->whereRelation('productVariant.product', 'entity_id', $v)),
            ]);

        $inventories = $query->latest()->paginate($perPage)->appends($request->query());

        return InventorySearchResource::collection($inventories);
    }

    public function autocomplete(Request $request)
    {
        $this->authorize('viewAny', ProductVariant::class);

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min(50, $perPage));

        $baseQuery = ProductVariant::query()
            ->with([
                'product.brand.category',
                'product.entity',
                'inventories',
            ])
            ->whereHas('product', function ($q2) {
                $q2->where('status', 'available');
            });

        // Apply Search Service for 'q'
        if ($q = $request->input('q')) {
            $this->autocompleteService->applySearch($baseQuery, $q, [
                'product.name',
                'product.code',
                'sku',
                'barcode',
            ]);
        }

        $query = QueryBuilder::for($baseQuery)
            ->allowedFilters(...[
                AllowedFilter::exact('product_id'),
                AllowedFilter::callback('category_id', fn ($q, $v) => $q->whereRelation('product.brand', 'category_id', $v)),
                AllowedFilter::callback('brand_id', fn ($q, $v) => $q->whereRelation('product', 'brand_id', $v)),
                AllowedFilter::callback('entity_id', fn ($q, $v) => $q->whereRelation('product', 'entity_id', $v)),
            ]);

        $variants = $query->latest()->paginate($perPage)->appends($request->query());

        return ProductVariantAutocompleteResource::collection($variants);
    }
}
