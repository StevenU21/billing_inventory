<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Rules\UniqueVariantSku;
use Elegantly\Money\Rules\ValidMoney;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Product::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('product'));
        }

        return false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $product = $this->route('product');

        if ($product instanceof Product) {
            if (! $this->has('id')) {
                $this->merge(['id' => $product->id]);
            }

            if (! $this->has('attributes')) {
                $this->merge(['attributes' => []]);
            }

            $fields = ['name', 'brand_id', 'tax_id', 'unit_measure_id', 'status', 'code'];
            foreach ($fields as $field) {
                if (! $this->has($field)) {
                    $value = $product->{$field};
                    if ($value instanceof \BackedEnum) {
                        $value = $value->value;
                    }
                    $this->merge([$field => $value]);
                }
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        return [
            'id' => ['nullable', 'integer', 'exists:products,id'],
            'status' => ['nullable', Rule::enum(ProductStatus::class)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],

            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'tax_id' => ['required', 'integer', 'exists:taxes,id'],
            'unit_measure_id' => ['required', 'integer', 'exists:unit_measures,id'],

            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['required', 'string', 'distinct', 'max:50'],

            'variants' => ['required', 'array', 'min:1'],

            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],

            'variants.*.attributes' => ['nullable', 'array'],

            'variants.*.sku' => ['nullable', 'string', 'distinct', new UniqueVariantSku],
            'variants.*.barcode' => ['nullable', 'string', 'max:50'],
            'variants.*.image' => ['nullable', 'image', 'max:2048'],
            'variants.*.price' => ['required', new ValidMoney(min: 0.01)],
            'variants.*.credit_price' => ['nullable', new ValidMoney(min: 0.01)],
            'variants.*.currency' => ['nullable', 'string', 'size:3', 'in:'.$currencies],
        ];
    }
}
