<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\PurchaseData;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Models\Entity;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Purchase::class);

        $purchases = QueryBuilder::for(Purchase::class)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('entity_id', 'supplier_id'),
                AllowedFilter::exact('payment_method_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
            ])
            ->allowedSorts(...['id', 'created_at', 'total', 'reference'])
            ->defaultSort('-created_at')
            ->withCount('details')
            ->with(['entity', 'paymentMethod', 'user', 'details.productVariant.product'])
            ->paginate(10)
            ->withQueryString();

        $latestPurchaseId = Purchase::max('id');

        $methods = PaymentMethod::pluck('name', 'id');
        $statuses = collect(PurchaseStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]);

        return view('admin.purchases.index', compact('purchases', 'methods', 'statuses', 'latestPurchaseId'));
    }

    public function create()
    {
        $this->authorize('create', Purchase::class);

        $entities = Entity::active()->where('is_supplier', true)->get()->pluck('short_name', 'id');

        $methods = PaymentMethod::pluck('name', 'id');
        $productVariants = ProductVariant::with(['product', 'attributeValues'])->get();

        return view('admin.purchases.create', compact('entities', 'methods', 'productVariants'));
    }

    public function store(PurchaseRequest $request, PurchaseService $purchaseService)
    {
        $this->authorize('create', Purchase::class);

        $purchaseData = PurchaseData::fromRequest($request->validated());
        $purchaseService->createPurchase($purchaseData, $request->user()->id);

        return redirect()->route('purchases.index')->with('success', 'Compra creada correctamente.');
    }

    public function show(Purchase $purchase)
    {
        $this->authorize('view', $purchase);
        $purchase->load([
            'entity',
            'user',
            'paymentMethod',
            'details.productVariant.product',
            'details.productVariant.attributeValues.attribute',
        ]);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase, PurchaseService $purchaseService)
    {
        $this->authorize('update', $purchase);

        $purchaseService->receivePurchase($purchase);

        return redirect()->route('purchases.index')->with('success', 'Compra recibida y stock actualizado correctamente.');
    }

    public function destroy(Purchase $purchase, PurchaseService $purchaseService)
    {
        $this->authorize('destroy', $purchase);

        $purchaseService->deletePurchase($purchase);

        return redirect()->route('purchases.index')->with('deleted', 'Compra eliminada y stock restaurado correctamente.');
    }

    public function edit(Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        $purchase->load('details.productVariant.product');

        $entities = Entity::active()->where('is_supplier', true)->get()->pluck('short_name', 'id');

        $methods = PaymentMethod::pluck('name', 'id');
        $productVariants = ProductVariant::with(['product', 'attributeValues'])->get();

        return view('admin.purchases.edit', compact('purchase', 'entities', 'methods', 'productVariants'));
    }

    public function update(PurchaseRequest $request, Purchase $purchase, PurchaseService $purchaseService)
    {
        $this->authorize('update', $purchase);

        $purchaseData = PurchaseData::fromRequest($request->validated());
        $purchaseService->updatePurchase($purchase, $purchaseData);

        return redirect()->route('purchases.index')->with('success', 'Compra actualizada correctamente.');
    }
}
