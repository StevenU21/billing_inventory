<?php

namespace App\Services;

use App\Contracts\SkuGeneratorInterface;
use App\DTOs\ProductVariantData;
use App\Exceptions\BusinessLogicException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ProductVariantService
{
    public function __construct(
        protected FileNativeService $fileService,
        protected SkuGeneratorInterface $skuGenerator,
        protected ProductAttributeService $attributeService
    ) {}

    public function sync(Product $product, array $items): void
    {
        $existingVariants = $product->variants()
            ->with(['attributeValues.attribute'])
            ->withCount(['purchaseDetails', 'saleDetails'])
            ->get();

        $variantsBySignature = $existingVariants->mapWithKeys(function ($v) {
            $signature = $this->generateVariantSignatureFromModel($v);

            return [$signature => $v];
        });

        $variantsById = $existingVariants->keyBy('id');

        $processedIds = [];

        foreach ($items as $itemData) {
            $variant = $this->resolveVariant($product, $itemData, $variantsById, $variantsBySignature);
            $this->persistVariant($product, $variant, $itemData);
            $processedIds[] = $variant->id;
        }

        $this->prune($existingVariants, $processedIds);
    }

    protected function resolveVariant(
        Product $product,
        ProductVariantData $itemData,
        Collection $variantsById,
        Collection $variantsBySignature
    ): ProductVariant {
        if ($itemData->id && $variantsById->has($itemData->id)) {
            return $variantsById->get($itemData->id);
        }

        $signature = $this->generateVariantSignatureFromData($itemData->attributes);

        if ($variantsBySignature->has($signature)) {
            return $variantsBySignature->get($signature);
        }

        return $product->variants()->make();
    }

    protected function persistVariant(Product $product, ProductVariant $variant, ProductVariantData $itemData): void
    {
        if ($variant->exists && $variant->has_commercial_movements) {
            $currentSignature = $this->generateVariantSignatureFromModel($variant);
            $requestedSignature = $this->generateVariantSignatureFromData($itemData->attributes);

            if ($currentSignature !== $requestedSignature) {
                throw new BusinessLogicException(
                    'No se pueden modificar los atributos de una variante que ya tiene compras o ventas registradas.',
                    'attributes'
                );
            }
        }

        $finalSku = $itemData->sku ? strtoupper(trim($itemData->sku)) : null;

        if (empty($finalSku)) {
            $finalSku = $variant->sku;
        }

        if (empty($finalSku)) {
            $finalSku = $this->skuGenerator->generate($product, $variant);
        }

        if ($variant->sku !== $finalSku && ProductVariant::where('sku', $finalSku)->where('id', '!=', $variant->id)->exists()) {
            throw new BusinessLogicException("El SKU {$finalSku} ya está ocupado.");
        }

        $variant->fill([
            'product_id' => $product->id,
            'sku' => $finalSku,
            'barcode' => $itemData->barcode,
            'price' => $itemData->price,
            'credit_price' => $itemData->creditPrice,
            'currency' => $itemData->currency,
            'cost' => $variant->cost,
            'search_text' => $this->generateSearchText($itemData->attributes),
        ]);

        if ($itemData->image) {
            $variant->image = $this->fileService->replace($variant, $itemData->image);
        }

        $variant->save();

        // Sync attributes only when the variant is still editable.
        if (! $variant->has_commercial_movements) {
            $this->attributeService->syncVariantAttributes($variant, $itemData->attributes);
        }
    }

    protected function generateVariantSignatureFromModel(ProductVariant $variant): string
    {
        $attrs = [];
        foreach ($variant->attributeValues as $val) {
            $attrs[$val->attribute->name] = $val->value;
        }

        return $this->generateVariantSignatureFromData($attrs);
    }

    protected function generateVariantSignatureFromData(array $attributes): string
    {
        ksort($attributes);

        return http_build_query($attributes);
    }

    protected function generateSearchText(array $attributes): string
    {
        return implode(' ', array_values($attributes));
    }

    protected function prune(Collection $existingVariants, array $keepIds): void
    {
        $toDelete = $existingVariants->except($keepIds);

        foreach ($toDelete as $variant) {
            $this->delete($variant);
        }
    }

    public function deleteAllForProduct(Product $product): void
    {
        foreach ($product->variants as $variant) {
            $this->delete($variant);
        }
    }

    public function delete(ProductVariant $variant): void
    {
        if ($variant->has_commercial_movements) {
            throw new BusinessLogicException(
                "No se puede borrar la variante {$variant->sku} porque ya tiene compras o ventas registradas.",
                'variant'
            );
        }

        if ($variant->inventories()->exists()) {
            if ($variant->inventories()->sum('stock') > 0) {
                throw new BusinessLogicException("No se puede borrar variante con stock: {$variant->sku}");
            }
        }

        $this->fileService->delete($variant);
        $variant->attributeValues()->detach();
        $variant->delete();
    }
}
