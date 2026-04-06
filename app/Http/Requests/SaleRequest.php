<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Services\CashRegisterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post') ? $this->user()->can('create', Sale::class) : false;
    }

    public function rules(): array
    {
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        return [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('entities', 'id')->where(function ($query) {
                    return $query->where('is_client', true);
                }),
            ],

            'payment_method_id' => [
                'required_unless:is_credit,1',
                'nullable',
                'integer',
                'exists:payment_methods,id',
            ],
            'is_credit' => ['nullable', 'boolean'],

            'sale_date' => ['nullable', 'date', 'before_or_equal:today'],

            'currency' => ['nullable', 'string', "in:$currencies"],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
                'distinct',
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.9999', new \App\Rules\FractionalQuantity],

            'items.*.discount' => ['nullable', 'boolean'],
            'items.*.discount_percentage' => [
                'nullable',
                'required_if:items.*.discount,true',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validar caja abierta solo para ventas al contado con método de pago en efectivo
            if ($this->shouldRequireCashRegister()) {
                $cashRegisterService = app(CashRegisterService::class);

                if (! $cashRegisterService->hasActiveSession($this->user()->id)) {
                    $validator->errors()->add(
                        'cash_register',
                        'Debe abrir una caja registradora antes de realizar ventas al contado en efectivo.'
                    );
                }
            }
        });
    }

    /**
     * Determine if this sale requires an open cash register.
     */
    private function shouldRequireCashRegister(): bool
    {
        // Si es venta a crédito, no requiere caja
        if ($this->boolean('is_credit')) {
            return false;
        }

        // Si no hay método de pago, no podemos validar
        $paymentMethodId = $this->input('payment_method_id');
        if (! $paymentMethodId) {
            return false;
        }

        // Verificar si el método de pago es efectivo
        return PaymentMethod::where('id', $paymentMethodId)
            ->where('is_cash', true)
            ->exists();
    }
}
