<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\QuotationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Entity;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QuotationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = QueryBuilder::for(Quotation::class)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('from'),
                AllowedFilter::scope('to'),
            ])
            ->allowedSorts(...['id', 'created_at', 'total', 'valid_until', 'status'])
            ->defaultSort('-created_at')
            ->with(['client', 'user', 'quotationDetails.productVariant.product'])
            ->withCount('quotationDetails')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $this->authorize('create', Quotation::class);

        $clients = Entity::where('is_active', true)->where('is_client', true)
            ->get()->pluck(fn ($e) => trim(($e->first_name ?? '').' '.($e->last_name ?? '')), 'id');
        $categories = Category::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');
        $suppliers = Entity::where('is_active', true)->where('is_supplier', true)
            ->get()->pluck(fn ($e) => trim(($e->first_name ?? '').' '.($e->last_name ?? '')), 'id');

        // Product variants list for items selector (reuse sales approach)
        $productVariants = ProductVariant::with(['product', 'attributeValues'])->get();

        // Supported currencies
        $currencies = array_combine(ProductVariant::SUPPORTED_CURRENCIES, ProductVariant::SUPPORTED_CURRENCIES);

        return view('admin.quotations.create', compact('clients', 'categories', 'brands', 'suppliers', 'productVariants', 'currencies'));
    }

    public function store(QuotationRequest $request, QuotationService $service)
    {
        $this->authorize('create', Quotation::class);

        $validated = $request->validated();

        $validated['user_id'] = Auth::id();
        $dto = QuotationData::fromRequest($validated);
        $service->createQuotation($dto);

        return redirect()->route('admin.quotations.index')->with('success', 'Cotización registrada correctamente.');
    }

    public function accept(Quotation $quotation, Request $request, QuotationService $service)
    {
        $this->authorize('update', $quotation);

        // El almacén se resolverá automáticamente desde el servicio según inventarios disponibles
        $service->acceptQuotation($quotation, Auth::id());

        return redirect()->route('admin.quotations.index')
            ->with('success', 'Proforma aceptada y venta registrada correctamente.');
    }

    public function cancel(Quotation $quotation, QuotationService $service)
    {
        $this->authorize('update', $quotation);
        $service->cancelQuotation($quotation);

        return back()->with('success', 'Proforma cancelada correctamente.');
    }
}
