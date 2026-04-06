<?php

namespace App\DTOs;

use Brick\Money\Money;

final class LedgerTotals
{
    public function __construct(
        public readonly Money $charge,
        public readonly Money $credit,
        public readonly Money $balance,
    ) {}
}
