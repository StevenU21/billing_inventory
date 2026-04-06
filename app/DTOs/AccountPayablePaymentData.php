<?php

namespace App\DTOs;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use App\Models\AccountPayable;

readonly class AccountPayablePaymentData
{
    public function __construct(
        public int $accountPayableId,
        public int $paymentMethodId,
        public Money $amount,
        public CarbonImmutable $paymentDate,
        public int $userId,
        public ?string $notes = null,
    ) {
    }

    public static function fromRequest(array $validatedData, AccountPayable $accountPayable): self
    {
        return new self(
            accountPayableId: $accountPayable->id,
            paymentMethodId: (int) $validatedData['payment_method_id'],
            amount: Money::of((string) $validatedData['amount'], $validatedData['currency'] ?? 'NIO'),
            paymentDate: isset($validatedData['payment_date'])
            ? CarbonImmutable::parse($validatedData['payment_date'])
            : CarbonImmutable::now(),
            userId: (int) $validatedData['user_id'],
            notes: isset($validatedData['notes']) ? trim((string) $validatedData['notes']) : null,
        );
    }
}
