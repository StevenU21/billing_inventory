<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Entity;
use App\Models\Municipality;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\DTOs\SaleData;
use App\Services\SaleService;

class SaleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $sales = QueryBuilder::for(Sale::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('payment_method_id'),
                AllowedFilter::exact('is_credit'),
                AllowedFilter::callback('from', function ($query, $value) {
                    $query->where('sale_date', '>=', Carbon::parse($value)->toDateString());
                }),
                AllowedFilter::callback('to', function ($query, $value) {
                    $query->where('sale_date', '<=', Carbon::parse($value)->toDateString());
                }),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['id', 'total', 'sale_date'])
            ->defaultSort('-id')
            ->with([
                'client:id,first_name,last_name',
                'user',
                'paymentMethod:id,name',
                'accountReceivable:id,sale_id,status',
                'saleDetails.productVariant.product:id,name'
            ])
            ->withCount('saleDetails')
            ->paginate(10)
            ->withQueryString();

        $methods = PaymentMethod::pluck('name', 'id');
        $latestSaleId = Sale::max('id');

        return view('admin.sales.index', compact('sales', 'methods', 'latestSaleId'));
    }

    public function create()
    {
        $this->authorize('create', Sale::class);

        $methods = PaymentMethod::all();
        $categories = Category::orderBy('name')->pluck('name', 'id')->toArray();
        $brands = Brand::orderBy('name')->pluck('name', 'id')->toArray();
        $suppliers = Entity::where('is_supplier', true)->selectRaw("CONCAT(first_name, ' ', last_name) as full_name, id")->pluck('full_name', 'id')->toArray();
        $municipalities = Municipality::orderBy('name')->pluck('name', 'id')->toArray();

        $clientEntities = Entity::where('is_client', true)->get();

        $productVariants = ProductVariant::with(['product', 'attributeValues'])->get();

        return view('admin.sales.create', compact('clientEntities', 'methods', 'categories', 'brands', 'suppliers', 'municipalities', 'productVariants'));
    }

    public function store(SaleRequest $request, SaleService $saleService)
    {
        $this->authorize('create', Sale::class);

        $saleService->createSale(
            SaleData::fromRequest($request->validated() + ['user_id' => $request->user()->id])
        );

        return redirect()->route('admin.sales.index')
            ->with('success', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load([
            'client',
            'user',
            'paymentMethod',
            'saleDetails.productVariant.product.brand',
            'saleDetails.productVariant.product.tax',
            'saleDetails.productVariant.attributeValues',
        ]);

        return view('admin.sales.show', compact('sale'));
    }

    public function destroy(Sale $sale, SaleService $saleService)
    {
        $this->authorize('delete', $sale);

        $saleService->cancelSale($sale);

        return redirect()->route('admin.sales.index')->with('deleted', 'Venta anulada y stock restaurado correctamente.');
    }
}
