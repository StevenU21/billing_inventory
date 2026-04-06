<?php

namespace App\DTOs;

use App\Models\Purchase;
use App\Models\Sale;
use Brick\Money\Money;
use Illuminate\Pagination\LengthAwarePaginator;

final class EntityFinancialShowData
{
    /**
     * Data Transfer Object for Entity Show Page
     */
    public function __construct(
        public readonly Money $saldoPendiente,
        public readonly Money $salesTotal,
        public readonly Money $purchasesTotal,
        public readonly ?Sale $lastSale,
        public readonly ?Purchase $lastPurchase,
        public readonly LengthAwarePaginator $sales,
        public readonly LengthAwarePaginator $purchases,
        public readonly LengthAwarePaginator $ledger,
        public readonly LedgerTotals $ledgerTotals,
    ) {}

    public function toArray(): array
    {
        return [
            'saldo_pendiente' => $this->saldoPendiente,
            'is_saldo_positive' => $this->saldoPendiente->isPositive(),
            'is_saldo_zero' => $this->saldoPendiente->isZero(),
            'sales_total' => $this->salesTotal,
            'purchases_total' => $this->purchasesTotal,
            'last_sale' => $this->lastSale,
            'last_purchase' => $this->lastPurchase,
            'sales' => $this->sales,
            'purchases' => $this->purchases,
            'ledger' => $this->ledger,
            'ledger_totals' => $this->ledgerTotals,
        ];
    }
}
