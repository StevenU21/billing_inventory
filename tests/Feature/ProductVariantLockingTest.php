<?php

namespace Tests\Feature;

use App\DTOs\ProductData;
use App\DTOs\ProductVariantData;
use App\Enums\ProductStatus;
use App\Exceptions\BusinessLogicException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_attributes_cannot_change_when_it_has_sales(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        SaleDetail::factory()->create([
            'sale_id' => Sale::factory()->create()->id,
            'product_variant_id' => $variant->id,
        ]);

        $data = new ProductData(
            name: $product->name,
            brandId: $product->brand_id,
            taxId: $product->tax_id,
            unitMeasureId: $product->unit_measure_id,
            items: [
                new ProductVariantData(
                    id: $variant->id,
                    sku: $variant->sku,
                    barcode: $variant->barcode,
                    price: Money::of('10.00', 'NIO'),
                    creditPrice: null,
                    currency: 'NIO',
                    image: null,
                    attributes: ['Color' => 'Rojo'],
                ),
            ],
            status: ProductStatus::Draft,
            description: $product->description,
            attributes: [],
            code: $product->code,
            image: null,
            id: $product->id,
        );

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('No se pueden modificar los atributos de una variante que ya tiene compras o ventas registradas.');

        app(ProductService::class)->updateProduct($product, $data);
    }

    public function test_variant_cannot_be_deleted_when_it_has_purchases(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        PurchaseDetail::factory()->create([
            'purchase_id' => Purchase::factory()->create()->id,
            'product_variant_id' => $variant->id,
        ]);

        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('No se puede borrar la variante');

        app(ProductVariantService::class)->delete($variant);
    }
}
