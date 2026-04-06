<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Entity;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Tax;
use App\Models\UnitMeasure;
use App\Enums\ProductStatus;
use App\Services\AutocompleteService;
use App\Services\ProductService;
use App\Exceptions\BusinessLogicException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize("viewAny", Product::class);

        $products = QueryBuilder::for(Product::class)
            ->select(['id', 'name', 'description', 'brand_id', 'image', 'tax_id', 'unit_measure_id', 'status', 'created_at', 'code'])
            ->withCount('variants')
            ->allowedFilters([
                AllowedFilter::exact('brand_id'),
                AllowedFilter::scope('search'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('category_id'),
                AllowedFilter::exact('unit_measure_id'),
            ])
            ->allowedSorts(['id', 'name', 'created_at', 'status'])
            ->defaultSort('-created_at')
            ->with(['brand.category', 'tax', 'variants.attributeValues.attribute', 'variants:id,product_id,price,currency,sku'])
            ->paginate(10)
            ->withQueryString();

        $categories = Category::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');
        $units = UnitMeasure::pluck('name', 'id');

        return view('admin.products.index', compact('products', 'categories', 'brands', 'units'));
    }

    public function create()
    {
        $this->authorize("create", Product::class);

        $product = new Product();

        $categories = Category::orderBy('name')->pluck('name', 'id');
        $units = UnitMeasure::orderBy('name')->pluck('name', 'id');
        $taxes = Tax::orderBy('name')->pluck('name', 'id');
        $brands = Brand::orderBy('name')->pluck('name', 'id');
        $availableAttributes = ProductAttribute::pluck('name');

        return view('admin.products.create', compact('product', 'categories', 'units', 'taxes', 'brands', 'availableAttributes'));
    }

    public function store(ProductRequest $request)
    {
        $this->authorize("create", Product::class);

        $data = ProductData::fromRequest($request->validated());

        $this->productService->createProduct($data);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $this->authorize("update", $product);

        $categories = Category::pluck('name', 'id');
        $units = UnitMeasure::pluck('name', 'id');
        $taxes = Tax::pluck('name', 'id');
        $brands = Brand::pluck('name', 'id');

        $product->load('brand:id,name');
        $availableAttributes = ProductAttribute::pluck('name');

        return view('admin.products.edit', compact('product', 'categories', 'units', 'taxes', 'brands', 'availableAttributes'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize("update", $product);

        $data = ProductData::fromRequest($request->validated());
        $this->productService->updateProduct($product, $data);

        return redirect()->route('products.index')->with('updated', 'Producto actualizado correctamente.');
    }

    public function show(Product $product)
    {
        $this->authorize("view", $product);

        $product->load([
            'brand.category',
            'tax',
            'unitMeasure',
            'variants.inventories',
            'variants.attributeValues.attribute', // Fix N+1 for variant_badges and variant display
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function destroy(Product $product)
    {
        $this->authorize("destroy", $product);

        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function autocomplete(Request $request, AutocompleteService $autocompleteService)
    {
        $this->authorize('viewAny', Product::class);

        $term = $request->input('q', '');
        $limit = $request->input('limit', 10);

        $query = Product::query()
            ->select('id', 'name', 'code')
            ->where('status', ProductStatus::Available);

        $searchFields = ['name', 'code'];

        $results = $autocompleteService->search($query, $term, $searchFields, $limit);

        return $autocompleteService->response($results, function ($p) {
            return [
                'id' => $p->id,
                'text' => "{$p->code} - {$p->name}",
            ];
        });
    }
}
