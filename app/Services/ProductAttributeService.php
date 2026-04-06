<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class ProductAttributeService
{
    public function ensureAttributesExist(array $config): void
    {
        foreach ($config as $attributeName) {
            $this->ensureAttribute($attributeName);
        }
    }

    public function ensureAttribute(string $name): ProductAttribute
    {
        return ProductAttribute::firstOrCreate(
            ['name' => $name],
            [
                'slug' => Str::slug($name),
                'is_filterable' => true
            ]
        );
    }

    public function ensureAttributeValue(string $attributeName, string $value): ProductAttributeValue
    {
        $attribute = $this->ensureAttribute($attributeName);

        return ProductAttributeValue::firstOrCreate(
            [
                'product_attribute_id' => $attribute->id,
                'value' => $value
            ],
            [
                'abbreviation' => Str::substr($value, 0, 3)
            ]
        );
    }

    public function syncVariantAttributes(ProductVariant $variant, array $attributes): void
    {
        $valueIds = [];

        foreach ($attributes as $name => $value) {
            if (empty($value)) {
                continue;
            }

            $attributeValue = $this->ensureAttributeValue($name, $value);
            $valueIds[] = $attributeValue->id;
        }

        $variant->attributeValues()->sync($valueIds);
    }
}
