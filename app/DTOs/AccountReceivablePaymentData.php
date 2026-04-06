<?php

namespace App\DTOs;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use App\Models\AccountReceivable;

readonly class AccountReceivablePaymentData
{
    public function __construct(
        public int $accountReceivableId,
        public int $paymentMethodId,
        public Money $amount,
        public CarbonImmutable $paymentDate,
        public int $userId,
        public ?string $notes = null,
    ) {
    }

    public static function fromRequest(array $validatedData, AccountReceivable $accountReceivable): self
    {
        return new self(
            accountReceivableId: $accountReceivable->id,
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
