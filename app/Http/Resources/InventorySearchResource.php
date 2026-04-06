<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventorySearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Inventory $inventory */
        $inventory = $this->resource;
        $variant = $inventory->productVariant;
        $product = optional($variant)->product;

        $calc = $inventory->calculated_price;

        return [
            'id' => $inventory->id,
            'text' => $product?->name,
            'label' => $calc['label'],
            'product_variant_id' => optional($variant)->id,
            'product_id' => optional($variant)->product_id,
            'product_name' => optional($product)->name,
            'variant_label' => (function () use ($variant) {
                if (!$variant)
                    return '';
                $options = $variant->attributeValues->pluck('value')->toArray();
                return !empty($options) ? implode(' / ', $options) : $variant->name;
            })(),
            'options' => $variant ? $variant->attributeValues->mapWithKeys(fn($av) => [$av->attribute->name => $av->value])->toArray() : [],
            'category_name' => optional($product?->brand?->category)->name,
            'brand_name' => optional($product?->brand)->name,
            'stock' => (int) ($inventory->stock ?? 0),
            'min_stock' => (int) ($inventory->min_stock ?? 0),
            'is_low_stock' => (bool) $inventory->is_below_min,
            'sale_price' => $calc['base'],
            'unit_price_with_tax' => $calc['final'],
            'unit_tax_amount' => $calc['tax_amount'],
            'tax_percentage' => $calc['tax_percent'],
            'image_url' => optional($variant)->image_url,
            'sku' => optional($variant)->sku,
            'code' => optional($product)->code,
            'barcode' => optional($variant)->barcode,
        ];
    }
}
