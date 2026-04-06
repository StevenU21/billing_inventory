<?php

namespace App\Services;

use App\Enums\AccountReceivableStatus;
use App\Models\AccountReceivable;
use App\Models\Sale;
use App\Exceptions\BusinessLogicException;
use Brick\Money\Money;
use RuntimeException;

class AccountReceivableService
{
    public function createFromSale(Sale $sale): AccountReceivable
    {
        if (!$sale->is_credit) {
            throw new BusinessLogicException("Cannot create Account Receivable for a non-credit sale.", 'is_credit');
        }

        if (!$sale->client_id) {
            throw new BusinessLogicException("Cannot create Account Receivable without a Client (Entity).", 'client_id');
        }

        if ($sale->accountReceivable()->exists()) {
            throw new RuntimeException("Sale #{$sale->id} already has an Account Receivable.");
        }

        return AccountReceivable::create([
            'sale_id' => $sale->id,
            'client_id' => $sale->client_id,
            'total_amount' => $sale->total,
            'balance' => $sale->total,
            'amount_paid' => Money::zero($sale->currency),
            'currency' => $sale->currency,
            'status' => AccountReceivableStatus::Pending,
        ]);
    }

    public function deleteForSale(Sale $sale): void
    {
        $ar = $sale->accountReceivable;

        if (!$ar) {
            return;
        }

        if ($ar->payments()->exists()) {
            throw new BusinessLogicException('No se puede anular una venta al crédito con pagos registrados. Por favor, elimine los pagos primero.', 'payments');
        }

        $ar->delete();
    }
}
