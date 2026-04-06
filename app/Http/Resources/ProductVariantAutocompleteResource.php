<?php

namespace App\Http\Resources;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantAutocompleteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\ProductVariant $variant */
        $variant = $this->resource;
        $product = $variant->product;

        // $variantParts = array_filter([$variant->option1, $variant->option2, $variant->option3]);
        $variantParts = $variant->attributeValues->pluck('value')->toArray();
        $variantLabel = implode(' / ', $variantParts);
        $fullLabel = $product->name . ($variantLabel ? " - $variantLabel" : "") . ($variant->sku ? " ({$variant->sku})" : "");

        // Helper to find specific attributes for legacy slots (optional)
        // We can try to map "Color" and "Size" if they exist, or just leave null
        $colorValue = $variant->attributeValues->first(fn($av) => stripos($av->attribute->name, 'color') !== false || stripos($av->attribute->name, 'colour') !== false)?->value;
        $sizeValue = $variant->attributeValues->first(fn($av) => stripos($av->attribute->name, 'size') !== false || stripos($av->attribute->name, 'talla') !== false)?->value;


        return [
            'id' => $variant->id,
            'text' => $fullLabel,
            'label' => $fullLabel,
            'product_id' => $variant->product_id,
            'product_name' => $product->name,
            'sku' => $variant->sku,

            'entity_name' => $product->relationLoaded('entity') ? optional($product->entity)->short_name : null,
            'color_name' => $colorValue,
            'size_name' => $sizeValue,
            // 'option3' => $variant->option3, // Add if UI uses it
            'category_name' => $product->brand?->category?->name,
            'brand_name' => $product->brand?->name,
            'image_url' => $variant->image_url,
        ];
    }
}
