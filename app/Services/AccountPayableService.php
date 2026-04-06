<?php

namespace App\Services;

use App\Enums\AccountPayableStatus;
use App\Exceptions\BusinessLogicException;
use App\Models\AccountPayable;
use App\Models\Purchase;
use Brick\Money\Money;
use RuntimeException;

class AccountPayableService
{
    public function createFromPurchase(Purchase $purchase): AccountPayable
    {
        if (! $purchase->is_credit) {
            throw new BusinessLogicException('No se puede crear una Cuenta por Pagar para una compra al contado.', 'is_credit');
        }

        if (! $purchase->supplier_id) {
            throw new BusinessLogicException('No se puede crear una Cuenta por Pagar sin un Proveedor.', 'supplier_id');
        }

        if ($purchase->accountPayable()->exists()) {
            throw new RuntimeException("La Compra #{$purchase->id} ya tiene una Cuenta por Pagar asociada.");
        }

        return AccountPayable::create([
            'purchase_id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'total_amount' => $purchase->total,
            'balance' => $purchase->total,
            'amount_paid' => Money::zero($purchase->currency),
            'currency' => $purchase->currency,
            'status' => AccountPayableStatus::Pending,
            'due_date' => now()->addDays(30),
        ]);
    }

    public function deleteForPurchase(Purchase $purchase): void
    {
        $ap = $purchase->accountPayable;

        if (! $ap) {
            return;
        }

        if ($ap->payments()->exists()) {
            throw new BusinessLogicException('No se puede anular una compra al crédito con pagos registrados. Por favor, elimine los pagos primero.', 'payments');
        }

        $ap->delete();
    }
}
