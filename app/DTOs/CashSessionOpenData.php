<?php

namespace App\DTOs;

use Brick\Money\Money;

readonly class CashSessionOpenData
{
    public function __construct(
        public Money $openingBalance,
        public int $userId,
        public ?int $openedByUserId = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(array $validatedData): self
    {
        $currency = $validatedData['currency'] ?? 'NIO';

        return new self(
            openingBalance: Money::of((string) $validatedData['opening_balance'], $currency),
            userId: (int) $validatedData['user_id'],
            openedByUserId: isset($validatedData['opened_by_user_id'])
            ? (int) $validatedData['opened_by_user_id']
            : null,
            notes: isset($validatedData['notes']) ? trim((string) $validatedData['notes']) : null,
        );
    }
}
