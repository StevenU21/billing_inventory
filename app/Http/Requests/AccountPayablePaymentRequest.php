<?php

namespace App\Http\Requests;

use App\Models\AccountPayablePayment;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Services\CashRegisterService;
use Elegantly\Money\Rules\ValidMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AccountPayablePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AccountPayablePayment::class);
    }

    public function rules(): array
    {
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        return [
            'account_payable_id' => ['required', 'integer', 'exists:account_payables,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'amount' => ['required', new ValidMoney(min: 0.01)],
            'currency' => ['nullable', 'string', "in:$currencies"],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->isCashPayment()) {
                $cashRegisterService = app(CashRegisterService::class);

                if (! $cashRegisterService->hasActiveSession($this->user()->id)) {
                    $validator->errors()->add(
                        'cash_register',
                        'Debe abrir una caja registradora antes de registrar pagos en efectivo.'
                    );
                }
            }
        });
    }

    /**
     * Check if the payment method is cash-based.
     */
    private function isCashPayment(): bool
    {
        $paymentMethodId = $this->input('payment_method_id');
        if (! $paymentMethodId) {
            return false;
        }

        return PaymentMethod::where('id', $paymentMethodId)
            ->where('is_cash', true)
            ->exists();
    }

    public function attributes(): array
    {
        return [
            'account_payable_id' => 'cuenta por pagar',
            'payment_method_id' => 'método de pago',
            'amount' => 'monto',
            'notes' => 'notas',
        ];
    }

    public function messages(): array
    {
        return [
            'account_payable_id.required' => 'La :attribute es obligatoria.',
            'account_payable_id.exists' => 'La :attribute seleccionada no existe.',
            'amount.required' => 'El :attribute es obligatorio.',
            'amount.min' => 'El :attribute debe ser mayor a cero.',
        ];
    }
}
