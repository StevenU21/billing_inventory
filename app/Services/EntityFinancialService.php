<?php

namespace App\Services;

use App\DTOs\EntityFinancialShowData;
use App\DTOs\LedgerEntryDTO;
use App\DTOs\LedgerTotals;
use App\Models\Entity;
use App\Models\Purchase;
use App\Models\Sale;
use App\Repositories\EntityLedgerRepository;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class EntityFinancialService
{
    private string $currency;

    public function __construct(
        protected EntityLedgerRepository $ledgerRepository,
    ) {
        $this->currency = config('money.default_currency', 'NIO');
    }

    public function forShow(Entity $entity): EntityFinancialShowData
    {
        $totals = $this->ledgerRepository->getGlobalTotals($entity);

        $ledgerPaginator = $this->ledgerRepository->getPaginatedLedger($entity, 20);

        $firstRow = $ledgerPaginator->items()[0] ?? null;
        $runningBalance = $this->toMoney($this->ledgerRepository->getPreviousBalance($entity, $firstRow));

        $collection = collect($ledgerPaginator->items())->map(function ($row) use (&$runningBalance) {
            return $this->mapRowToDto($row, $runningBalance);
        });

        $ledgerPaginator->setCollection($collection);

        return new EntityFinancialShowData(
            saldoPendiente: $this->toMoney($totals['balance']),
            salesTotal: $this->toMoney($totals['sales_total']),
            purchasesTotal: $this->toMoney($totals['purchases_total']),
            lastSale: $this->lastSale($entity),
            lastPurchase: $this->lastPurchase($entity),
            sales: $this->recentTransactionsPaginated($entity->sales(), 'sale_date'),
            purchases: $this->recentTransactionsPaginated($entity->purchases(), 'purchase_date'),
            ledger: $ledgerPaginator,
            ledgerTotals: new LedgerTotals(
                charge: $this->toMoney($totals['debits']),
                credit: $this->toMoney($totals['credits']),
                balance: $this->toMoney($totals['balance']),
            ),
        );
    }

    public function lastSale(Entity $entity): ?Sale
    {
        return $entity->sales()->latest('sale_date')->first();
    }

    public function lastPurchase(Entity $entity): ?Purchase
    {
        return $entity->purchases()->latest('purchase_date')->first();
    }

    private function recentTransactionsPaginated($query, string $dateColumn, int $perPage = 10): LengthAwarePaginator
    {
        return $query->latest($dateColumn)
            ->paginate($perPage, ['id', 'total', $dateColumn, 'created_at', 'currency'])
            ->withQueryString();
    }

    private function mapRowToDto(object $row, Money &$runningBalance): LedgerEntryDTO
    {
        $debit = $this->toMoney((int) ($row->debit ?? 0));
        $credit = $this->toMoney((int) ($row->credit ?? 0));

        $runningBalance = $runningBalance->plus($debit)->minus($credit);

        $type = (string) ($row->type ?? '');

        $description = match ($type) {
            EntityLedgerRepository::TYPE_INVOICE => 'Factura #'.($row->sale_id ?? $row->ref_id),
            EntityLedgerRepository::TYPE_PAYMENT => 'Pago #'.($row->payment_id ?? $row->ref_id),
            default => 'Ref #'.$row->ref_id,
        };

        return new LedgerEntryDTO(
            date: CarbonImmutable::parse($row->date),
            description: $description,
            debit: $debit,
            credit: $credit,
            balance: $runningBalance,
            type: $type,
            saleId: isset($row->sale_id) ? (int) $row->sale_id : null,
            accountReceivableId: isset($row->account_receivable_id) ? (int) $row->account_receivable_id : null,
        );
    }

    private function toMoney(int $amount): Money
    {
        return Money::ofMinor((string) $amount, $this->currency);
    }
}
