<?php

namespace App\Services;

use App\DTOs\AccountPayablePaymentData;
use App\Enums\AccountPayableStatus;
use App\Exceptions\BusinessLogicException;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class AccountPayablePaymentService
{
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    public function createPayment(AccountPayablePaymentData $data): AccountPayablePayment
    {
        return DB::transaction(function () use ($data) {
            $accountPayable = $this->lockAccountPayable($data->accountPayableId);

            $this->validateCurrency($data->amount->getCurrency()->getCurrencyCode(), $accountPayable->currency);

            $this->validatePaymentAmount($data->amount, $accountPayable->balance);

            $payment = $this->storePayment($data, $accountPayable);
            $this->updateAccountPayable($accountPayable, $data->amount);

            $this->cashRegisterService->recordPayablePayment(
                paymentId: $payment->id,
                amount: $data->amount,
                userId: $data->userId,
                paymentMethodId: $data->paymentMethodId
            );

            return $payment;
        });
    }

    private function storePayment(
        AccountPayablePaymentData $data,
        AccountPayable $accountPayable
    ): AccountPayablePayment {
        return AccountPayablePayment::create([
            'account_payable_id' => $accountPayable->id,
            'supplier_id' => $accountPayable->supplier_id,
            'user_id' => $data->userId,
            'amount' => $data->amount,
            'payment_date' => $data->paymentDate,
            'payment_method_id' => $data->paymentMethodId,
            'reference' => $data->notes,
            'notes' => $data->notes,
            'currency' => $data->amount->getCurrency()->getCurrencyCode(),
        ]);
    }

    private function lockAccountPayable(int $accountPayableId): AccountPayable
    {
        return AccountPayable::query()
            ->lockForUpdate()
            ->findOrFail($accountPayableId);
    }

    private function validateCurrency(string $paymentCurrency, string $accountCurrency): void
    {
        if ($paymentCurrency !== $accountCurrency) {
            throw new BusinessLogicException("La moneda del pago ({$paymentCurrency}) no coincide con la moneda de la cuenta ({$accountCurrency}).");
        }
    }

    private function validatePaymentAmount(Money $paymentAmount, Money $currentBalance): void
    {
        if ($paymentAmount->isGreaterThan($currentBalance)) {
            throw new BusinessLogicException("El monto del pago ({$paymentAmount}) excede el saldo pendiente ({$currentBalance}).", 'amount');
        }
    }

    private function updateAccountPayable(AccountPayable $accountPayable, Money $paymentAmount): void
    {
        $accountPayable->amount_paid = $accountPayable->amount_paid->plus($paymentAmount);

        $accountPayable->balance = $accountPayable->total_amount->minus($accountPayable->amount_paid);

        $accountPayable->status = $this->determineStatus($accountPayable->balance);
        $accountPayable->save();
    }

    private function determineStatus(Money $balance): AccountPayableStatus
    {
        return $balance->isZero() || $balance->isNegative()
            ? AccountPayableStatus::Paid
            : AccountPayableStatus::PartiallyPaid;
    }
}
