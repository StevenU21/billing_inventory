<?php

namespace App\Services;

use App\DTOs\AccountReceivablePaymentData;
use App\Enums\AccountReceivableStatus;
use App\Events\AccountReceivablePaymentCreated;
use App\Exceptions\BusinessLogicException;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use Illuminate\Support\Facades\DB;

class AccountReceivablePaymentService
{
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    public function createPayment(AccountReceivablePaymentData $data): AccountReceivablePayment
    {
        return DB::transaction(function () use ($data) {
            $accountReceivable = $this->lockAccountReceivable($data->accountReceivableId);

            $this->validateCurrency($data->amount->getCurrency()->getCurrencyCode(), $accountReceivable->currency);

            $this->validatePaymentAmount($data->amount, $accountReceivable->balance);

            $payment = $this->storePayment($data, $accountReceivable);
            $this->updateAccountReceivable($accountReceivable, $data->amount);

            $this->cashRegisterService->recordReceivablePayment(
                paymentId: $payment->id,
                amount: $data->amount,
                userId: $data->userId,
                paymentMethodId: $data->paymentMethodId
            );

            AccountReceivablePaymentCreated::dispatch($payment);

            return $payment;
        });
    }

    private function storePayment(
        AccountReceivablePaymentData $data,
        AccountReceivable $accountReceivable
    ): AccountReceivablePayment {
        return AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'payment_method_id' => $data->paymentMethodId,
            'amount' => $data->amount,
            'payment_date' => $data->paymentDate,
            'user_id' => $data->userId,
            'client_id' => $accountReceivable->client_id,
            'currency' => $accountReceivable->currency,
            'notes' => $data->notes,
        ]);
    }

    private function lockAccountReceivable(int $accountReceivableId): AccountReceivable
    {
        return AccountReceivable::query()
            ->lockForUpdate()
            ->findOrFail($accountReceivableId);
    }

    private function validateCurrency(string $paymentCurrency, string $accountCurrency): void
    {
        if ($paymentCurrency !== $accountCurrency) {
            throw new BusinessLogicException("La moneda del pago ({$paymentCurrency}) no coincide con la moneda de la cuenta ({$accountCurrency}).");
        }
    }

    private function validatePaymentAmount($paymentAmount, $currentBalance): void
    {
        if ($paymentAmount->isGreaterThan($currentBalance)) {
            throw new BusinessLogicException(__('El monto del pago (:amount) excede el saldo pendiente (:balance).', [
                'amount' => $paymentAmount->formatToLocale('es_NI'),
                'balance' => $currentBalance->formatToLocale('es_NI'),
            ]));
        }
    }

    private function updateAccountReceivable(AccountReceivable $accountReceivable, $paymentAmount): void
    {
        $accountReceivable->amount_paid = $accountReceivable->amount_paid->plus($paymentAmount);

        $accountReceivable->balance = $accountReceivable->total_amount->minus($accountReceivable->amount_paid);

        $accountReceivable->status = $this->determineStatus($accountReceivable->balance);
        $accountReceivable->save();
    }

    private function determineStatus($balance): AccountReceivableStatus
    {
        return $balance->isZero()
            ? AccountReceivableStatus::Paid
            : AccountReceivableStatus::PartiallyPaid;
    }
}
