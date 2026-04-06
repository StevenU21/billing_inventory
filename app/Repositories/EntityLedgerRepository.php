<?php

namespace App\Repositories;

use App\Enums\SaleStatus;
use App\Models\Entity;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EntityLedgerRepository
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_PAYMENT = 'payment';

    /**
     * Obtiene totales globales de forma eficiente.
     * Retorna enteros (minor units) listos para Money::ofMinor.
     *
     * @return array{debits: int, credits: int, balance: int, sales_total: int, purchases_total: int}
     */
    public function getGlobalTotals(Entity $entity): array
    {
        $debits = (int) $entity->accountReceivables()->toBase()->sum('total_amount');
        $credits = (int) $entity->payments()->toBase()->sum('amount');

        $salesTotal = (int) $entity->sales()
            ->where('status', '!=', SaleStatus::Cancelled->value)
            ->toBase()
            ->sum('total');
        $purchasesTotal = (int) $entity->purchases()->toBase()->sum('total');

        return [
            'debits' => $debits,
            'credits' => $credits,
            'balance' => $debits - $credits,
            'sales_total' => $salesTotal,
            'purchases_total' => $purchasesTotal,
        ];
    }

    /**
     * Construye y ejecuta la query del ledger paginado usando UNION ALL.
     */
    public function getPaginatedLedger(Entity $entity, int $perPage): LengthAwarePaginator
    {
        $invoices = DB::table('account_receivables')
            ->where('client_id', $entity->id)
            ->select([
                'created_at as date',
                DB::raw("'" . self::TYPE_INVOICE . "' as type"),
                'id as ref_id',
                'sale_id',
                'id as account_receivable_id',
                DB::raw('NULL as payment_id'),
                'total_amount as debit',
                DB::raw('0 as credit'),
            ]);

        $payments = DB::table('account_receivables_payments')
            ->where('client_id', $entity->id)
            ->select([
                DB::raw('COALESCE(payment_date, created_at) as date'),
                DB::raw("'" . self::TYPE_PAYMENT . "' as type"),
                'id as ref_id',
                DB::raw('NULL as sale_id'),
                'account_receivable_id',
                'id as payment_id',
                DB::raw('0 as debit'),
                'amount as credit',
            ]);

        /** @var Builder $union */
        $union = $invoices->unionAll($payments);

        $query = DB::query()
            ->fromSub($union, 'ledger')
            ->orderBy('date')
            ->orderByRaw("CASE WHEN type = '" . self::TYPE_INVOICE . "' THEN 0 ELSE 1 END")
            ->orderBy('ref_id');

        return $query->paginate($perPage, ['*'], 'ledger_page')->withQueryString();
    }

    /**
     * Calcula la suma de todo lo ANTERIOR a la página actual.
     * REQUIERE INDICES EN BD: (client_id, created_at) y (client_id, payment_date)
     */
    public function getPreviousBalance(Entity $entity, ?object $firstRow): int
    {
        if ($firstRow === null || empty($firstRow->date)) {
            return 0;
        }

        $firstDate = (string) $firstRow->date;
        $isInvoice = ((string) ($firstRow->type ?? self::TYPE_INVOICE)) === self::TYPE_INVOICE;
        $firstRefId = (int) ($firstRow->ref_id ?? 0);

        $prevDebits = (int) DB::table('account_receivables')
            ->where('client_id', $entity->id)
            ->where(function ($q) use ($firstDate, $isInvoice, $firstRefId) {
                $q->where('created_at', '<', $firstDate);
                $q->orWhere(function ($q) use ($firstDate, $isInvoice, $firstRefId) {
                    $q->where('created_at', '=', $firstDate);
                    if (!$isInvoice) {
                        return;
                    }
                    $q->where('id', '<', $firstRefId);
                });
            })
            ->sum('total_amount');

        $prevCredits = (int) DB::table('account_receivables_payments')
            ->where('client_id', $entity->id)
            ->where(function ($q) use ($firstDate, $isInvoice, $firstRefId) {
                $q->whereRaw('COALESCE(payment_date, created_at) < ?', [$firstDate]);
                $q->orWhere(function ($q) use ($firstDate, $isInvoice, $firstRefId) {
                    $q->whereRaw('COALESCE(payment_date, created_at) = ?', [$firstDate]);
                    if ($isInvoice) {
                        $q->whereRaw('1 = 0');
                        return;
                    }
                    $q->where('id', '<', $firstRefId);
                });
            })
            ->sum('amount');

        return $prevDebits - $prevCredits;
    }
}
