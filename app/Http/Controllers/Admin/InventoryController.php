<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\InventoryData;
use App\DTOs\InventoryMovementData;
use App\Enums\AdjustmentReason;
use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Tax;
use App\Services\InventoryService;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Inventory::class);

        $inventories = QueryBuilder::for(Inventory::class)
            ->allowedFilters([
                'id',
                'product_variant_id',
                'stock',
                'min_stock',
                AllowedFilter::scope('product', 'byProduct'),
                AllowedFilter::scope('category_id', 'byCategory'),
                AllowedFilter::scope('brand_id', 'byBrand'),
                AllowedFilter::scope('search'),
                AllowedFilter::scope('stock_level'),
                AllowedFilter::scope('tax_id', 'byTax'),
            ])
            ->allowedSorts([
                'id',
                'stock',
                'min_stock',
                'purchase_price',
                'sale_price',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->with([
                'productVariant.product.tax',
                'productVariant.product.unitMeasure',
                'productVariant.product.brand.category',
                'productVariant.attributeValues',
            ])
            ->paginate((int) $request->input('per_page', 10))
            ->appends($request->query());

        $categories = Category::pluck('name', 'id');

        $brandsQuery = Brand::query();
        if ($categoryId = $request->input('filter.category_id')) {
            $brandsQuery->where('category_id', $categoryId);
        }
        $brands = $brandsQuery->pluck('name', 'id');

        $taxes = Tax::pluck('name', 'id');
        $stockLevels = [
            'in_stock' => 'En Stock',
            'low_stock' => 'Bajo Stock',
            'out_of_stock' => 'Sin Stock',
        ];

        return view('admin.inventories.index', compact(
            'inventories',
            'categories',
            'brands',
            'taxes',
            'stockLevels'
        ));
    }

    public function create()
    {
        $this->authorize('create', Inventory::class);

        $variants = ProductVariant::with(['product', 'inventories', 'attributeValues.attribute'])
            ->get()
            ->mapWithKeys(function ($variant) {
                $name = $variant->product->name ?? 'Producto desconocido';
                $details = $variant->attributeValues->pluck('value')->join(' / ');

                $label = $details ? "{$name} - {$details}" : $name;

                return [$variant->id => $label." (SKU: {$variant->sku})"];
            });

        $currencies = ProductVariant::SUPPORTED_CURRENCIES;

        return view('admin.inventories.create', compact('variants', 'currencies'));
    }

    public function store(InventoryRequest $request, InventoryService $service)
    {
        $this->authorize('create', Inventory::class);
        $validated = $request->validated();

        $inventoryData = InventoryData::fromRequest($validated);

        $initialMovement = null;
        if (isset($validated['stock']) && $validated['stock'] > 0) {
            $initialMovement = new InventoryMovementData(
                type: InventoryMovementType::AdjustmentIn,
                quantity: BigDecimal::of((string) $validated['stock']),
                currency: $validated['currency'],
                unitPrice: isset($validated['unit_price']) ? BigDecimal::of((string) $validated['unit_price']) : null,
                reference: 'Inventario Inicial',
                notes: 'Primer registro de inventario'
            );
        }

        $service->createInventory($inventoryData, $initialMovement, auth()->id());

        return redirect()->route('inventories.index')->with('success', 'Inventario creado correctamente.');
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory);
        $inventory->load([
            'productVariant.product.tax',
            'productVariant.product.unitMeasure',
            'productVariant.product.brand.category',
            'productVariant.attributeValues',
        ]);

        $movements = $inventory->inventoryMovements()
            ->with('user')
            ->latest()
            ->paginate(15);

        // Calculate totals using multiplier: 1 = entry, -1 = exit
        $allMovements = $inventory->inventoryMovements()->get();

        $totalIn = $allMovements
            ->filter(fn ($m) => $m->type->multiplier() === 1)
            ->sum('quantity');

        $totalOut = $allMovements
            ->filter(fn ($m) => $m->type->multiplier() === -1)
            ->sum('quantity');

        return view('admin.inventories.show', compact('inventory', 'movements', 'totalIn', 'totalOut'));
    }

    public function edit(Inventory $inventory)
    {
        $this->authorize('update', $inventory);

        $inventory->load(['productVariant.product']);

        $movementTypes = [
            InventoryMovementType::AdjustmentIn->value => InventoryMovementType::AdjustmentIn->label(),
            InventoryMovementType::AdjustmentOut->value => InventoryMovementType::AdjustmentOut->label(),
        ];

        $adjustmentReasons = collect(AdjustmentReason::cases())
            ->mapWithKeys(fn ($r) => [$r->value => $r->label()]);

        return view('admin.inventories.edit', compact('inventory', 'movementTypes', 'adjustmentReasons'));
    }

    public function update(InventoryRequest $request, Inventory $inventory, InventoryService $inventoryService)
    {
        $this->authorize('update', $inventory);
        $validated = $request->validated();

        $dataForDto = $validated;
        $dataForDto['product_variant_id'] = $dataForDto['product_variant_id'] ?? $inventory->product_variant_id;

        $dataForDto['currency'] = $dataForDto['currency'] ?? $inventory->currency;

        $inventoryData = InventoryData::fromRequest($dataForDto, $inventory->id);

        $movementData = null;
        if (! empty($validated['movement_type'])) {
            $movementData = InventoryMovementData::fromRequest($validated + [
                'inventory_id' => $inventory->id,
                'currency' => $inventory->currency,
            ]);
        }

        $inventoryService->updateInventory($inventory, $inventoryData, $movementData, auth()->id());

        return redirect()->route('inventories.index')->with('updated', 'Inventario actualizado correctamente.');
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('destroy', $inventory);

        $inventory->delete();

        return redirect()->route('inventories.index')->with('deleted', 'Inventario eliminado correctamente.');
    }
}
