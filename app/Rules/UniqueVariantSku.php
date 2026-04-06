<?php

namespace App\Rules;

use App\Models\ProductVariant;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UniqueVariantSku implements ValidationRule, DataAwareRule
{
    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $index = explode('.', $attribute)[1];

        $variantId = data_get($this->data, "variants.{$index}.id");

        $rule = Rule::unique(ProductVariant::class, 'sku');

        if ($variantId) {
            $rule->ignore($variantId);
        }

        $validator = Validator::make(
            ['sku' => $value],
            ['sku' => $rule]
        );

        if ($validator->fails()) {
            $fail("El SKU '{$value}' ya está registrado.");
        }
    }
}
