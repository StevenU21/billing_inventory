<?php

namespace App\DTOs;

use Brick\Money\Money;
use DateTimeInterface;

final class LedgerEntryDTO
{
    public function __construct(
        public readonly DateTimeInterface $date,
        public readonly string $description,
        public readonly Money $debit,
        public readonly Money $credit,
        public readonly Money $balance,
        public readonly string $type,
        public readonly ?int $saleId = null,
        public readonly ?int $accountReceivableId = null,
    ) {}
}
