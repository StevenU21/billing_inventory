<?php

namespace App\Observers;

use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantObserver
{
    public function creating(ProductVariant $variant): void
    {
        if (empty($variant->sku)) {
            if (!$variant->relationLoaded('product')) {
                $variant->load('product');
            }

            $productCode = $variant->product ? $variant->product->code : 'UNK';

            // Generate SKU part from attributes
            // We take the first 3 letters of each attribute value
            $attrParts = $variant->attributeValues
                ->sortBy('attribute.name') // Consistent order
                ->map(fn($val) => strtoupper(substr(Str::slug($val->value), 0, 3)))
                ->join('-');

            $variant->sku = $attrParts
                ? "{$productCode}-{$attrParts}"
                : "{$productCode}-STD-" . strtoupper(Str::random(4)); // Fallback if no attributes
        }
    }
}
