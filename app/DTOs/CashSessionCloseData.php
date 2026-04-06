<?php

namespace App\DTOs;

use Brick\Money\Money;

readonly class CashSessionCloseData
{
    public function __construct(
        public int $sessionId,
        public Money $actualClosingBalance,
        public int $closedByUserId,
        public ?string $closingNotes = null,
    ) {}

    public static function fromRequest(array $validatedData, int $sessionId): self
    {
        $currency = $validatedData['currency'] ?? 'NIO';

        return new self(
            sessionId: $sessionId,
            actualClosingBalance: Money::of((string) $validatedData['actual_closing_balance'], $currency),
            closedByUserId: (int) $validatedData['closed_by_user_id'],
            closingNotes: isset($validatedData['closing_notes']) ? trim((string) $validatedData['closing_notes']) : null,
        );
    }
}
