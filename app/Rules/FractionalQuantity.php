<?php

namespace App\Rules;

use App\Models\ProductVariant;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class FractionalQuantity implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $attribute example: "items.0.quantity" or "details.0.quantity"
        $segments = explode('.', $attribute);
        $field = array_pop($segments);
        $prefix = implode('.', $segments);

        // Try to find the variant ID in the same array item
        // Standard naming in this app is 'product_variant_id'
        $variantId = data_get($this->data, $prefix . '.product_variant_id');

        if (!$variantId) {
            return;
        }

        $variant = ProductVariant::with('product.unitMeasure')->find($variantId);

        if (!$variant || !$variant->product || !$variant->product->unitMeasure) {
            return;
        }

        $allowsDecimals = $variant->product->unitMeasure->allows_decimals;

        // If decimals are NOT allowed, we must check if value is integer
        if (!$allowsDecimals) {
            // Check if it has a fractional part
            // We use a small epsilon for float comparison or just string check
            if ((float) $value != (int) $value) {
                $unitName = $variant->product->unitMeasure->name;
                $fail("El producto '{$variant->product->name}' utiliza la unidad '{$unitName}' que no permite cantidades fraccionarias.");
            }
        }
    }
}
