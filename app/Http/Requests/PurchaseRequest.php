<?php

namespace App\Http\Requests;

use App\Models\ProductVariant;
use App\Models\Purchase;
use Elegantly\Money\Rules\ValidMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Purchase::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('purchase'));
        }

        return false;
    }

    public function rules(): array
    {
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('purchases', 'reference')->ignore($this->route('purchase'))],
            'is_credit' => ['nullable', 'boolean'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'currency' => ['nullable', 'string', "in:$currencies"],

            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('entities', 'id')->where(function ($query) {
                    return $query->where('is_supplier', true);
                }),
            ],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id', 'distinct'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.9999', new \App\Rules\FractionalQuantity],
            'details.*.unit_price' => ['required', new ValidMoney(min: 0.01)],
            'details.*.tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}