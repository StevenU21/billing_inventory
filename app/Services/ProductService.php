<?php

namespace App\Services;

use App\Contracts\SkuGeneratorInterface;
use App\DTOs\ProductData;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected FileNativeService $fileService,
        protected ProductAttributeService $attributeService,
        protected ProductVariantService $variantService,
        protected SkuGeneratorInterface $skuGenerator
    ) {}

    public function createProduct(ProductData $data): Product
    {
        return $this->sync($data, null);
    }

    public function updateProduct(Product $product, ProductData $data): Product
    {
        return $this->sync($data, $product);
    }

    public function toggleStatus(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $product = $product->fresh();
            $product->status = $product->status === ProductStatus::Available
                ? ProductStatus::Archived
                : ProductStatus::Available;
            $product->save();

            return $product;
        });
    }

    public function getStatusEnums(): array
    {
        return ProductStatus::cases();
    }

    public function sync(ProductData $data, ?Product $product = null): Product
    {
        return DB::transaction(fn () => $this->executeSync($data, $product));
    }

    protected function executeSync(ProductData $data, ?Product $product = null): Product
    {
        if (! empty($data->attributes)) {
            $this->attributeService->ensureAttributesExist($data->attributes);
        }

        $attributes = [
            'name' => $data->name,
            'brand_id' => $data->brandId,
            'tax_id' => $data->taxId,
            'unit_measure_id' => $data->unitMeasureId,
            'description' => $data->description,
            'status' => $data->status,
        ];

        if (! empty($data->code)) {
            $attributes['code'] = $data->code;
        } elseif ($product && ! empty($product->code)) {

        } else {
            $tempSubject = $product ?? new Product(['name' => $data->name]);
            $attributes['code'] = $this->skuGenerator->generate($tempSubject);
        }

        if ($product) {
            $product->fill($attributes);
        } else {
            $product = new Product($attributes);
        }

        if ($data->image) {
            $product->image = $this->fileService->replace($product, $data->image);
            $product->saveQuietly();
        } else {
            $product->save();
        }

        $this->variantService->sync($product, $data->items);

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->variantService->deleteAllForProduct($product);
            $this->fileService->delete($product);
            $product->delete();
        });
    }
}
