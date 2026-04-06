<?php

namespace App\Services;

use App\DTOs\InventoryMovementData;
use App\DTOs\PurchaseData;
use App\Enums\InventoryMovementType;
use App\Enums\PurchaseStatus;
use App\Events\PurchaseReceived;
use App\Exceptions\BusinessLogicException;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CashRegisterService $cashRegisterService
    ) {
    }

    public function createPurchase(PurchaseData $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $calculation = $this->calculateTotals($data);

            $purchase = Purchase::create([
                'reference' => $data->reference,
                'purchase_date' => $data->purchaseDate,
                'status' => PurchaseStatus::Draft,
                'currency' => $data->currency,
                'supplier_id' => $data->supplierId,
                'payment_method_id' => $data->paymentMethodId,
                'user_id' => $userId,
                'sub_total' => $calculation['sub_total'],
                'total' => $calculation['total'],
                'tax_amount' => $calculation['tax_amount'],
                'is_credit' => $data->isCredit ?? false,
            ]);

            $purchase->details()->createMany($calculation['details']);

            return $purchase;
        });
    }

    public function updatePurchase(Purchase $purchase, PurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {

            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if ($purchase->status !== PurchaseStatus::Draft) {
                throw new BusinessLogicException('Solo se pueden editar compras en estado Borrador.', 'status');
            }

            $calculation = $this->calculateTotals($data);

            $purchase->update([
                'reference' => $data->reference,
                'purchase_date' => $data->purchaseDate,
                'currency' => $data->currency,
                'supplier_id' => $data->supplierId,
                'payment_method_id' => $data->paymentMethodId,
                'sub_total' => $calculation['sub_total'],
                'total' => $calculation['total'],
                'tax_amount' => $calculation['tax_amount'],
                'is_credit' => $data->isCredit ?? false,
            ]);

            $purchase->details()->delete();
            $purchase->details()->createMany($calculation['details']);

            return $purchase->refresh();
        });
    }

    public function receivePurchase(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::with('details')->lockForUpdate()->find($purchase->id);

            if ($purchase->status === PurchaseStatus::Received) {
                return $purchase;
            }

            if ($purchase->status !== PurchaseStatus::Ordered && $purchase->status !== PurchaseStatus::Draft) {
                throw new BusinessLogicException("La compra debe estar 'Borrador' u 'Ordenada' antes de recibirse.", 'status');
            }

            foreach ($purchase->details as $detail) {
                $unitCostAmount = BigDecimal::of($detail->unit_price->getAmount());

                $movementData = new InventoryMovementData(
                    inventoryId: null,
                    productVariantId: $detail->product_variant_id,
                    type: InventoryMovementType::Purchase,
                    quantity: BigDecimal::of($detail->quantity),
                    currency: $purchase->currency,
                    unitPrice: $unitCostAmount,
                    notes: "Recepción Compra #{$purchase->reference}",
                    reference: "PURCHASE-{$purchase->id}"
                );

                $this->inventoryService->processMovement(
                    data: $movementData,
                    userId: auth()->id(),
                    sourceType: PurchaseDetail::class,
                    sourceId: $detail->id
                );
            }

            if (!$purchase->is_credit) {
                $this->cashRegisterService->recordPurchaseMovement(
                    purchaseId: $purchase->id,
                    amount: $purchase->total,
                    userId: auth()->id(),
                    paymentMethodId: $purchase->payment_method_id
                );
            }

            $purchase->status = PurchaseStatus::Received;
            $purchase->received_at = now();
            $purchase->save();

            PurchaseReceived::dispatch($purchase);

            return $purchase;
        });
    }

    public function placeOrder(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if ($purchase->status !== PurchaseStatus::Draft) {
                throw new BusinessLogicException('Solo compras en Borrador pueden ser Ordenadas.', 'status');
            }

            $purchase->status = PurchaseStatus::Ordered;
            $purchase->save();

            return $purchase;
        });
    }

    public function cancelPurchase(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if ($purchase->status === PurchaseStatus::Received) {
                throw new BusinessLogicException('No se puede cancelar una compra ya recibida. Gestione una devolución.', 'status');
            }

            $purchase->status = PurchaseStatus::Cancelled;
            $purchase->save();

            return $purchase;
        });
    }

    public function deletePurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->find($purchase->id);

            if ($purchase->status !== PurchaseStatus::Draft) {
                throw new BusinessLogicException('Solo se pueden eliminar borradores.', 'status');
            }

            $purchase->details()->delete();
            $purchase->delete();
        });
    }

    private function calculateTotals(PurchaseData $data): array
    {
        $subTotal = Money::zero($data->currency);
        $totalTax = Money::zero($data->currency);
        $detailsPayload = [];

        foreach ($data->items as $item) {
            $unitPrice = Money::of($item->unitPrice, $data->currency);
            $qty = BigDecimal::of($item->quantity);

            $lineSubtotal = $unitPrice->multipliedBy($qty, RoundingMode::HALF_UP);
            $taxAmount = $lineSubtotal->multipliedBy($item->taxPercentage, RoundingMode::HALF_UP)
                ->dividedBy(100, RoundingMode::HALF_UP);

            $subTotal = $subTotal->plus($lineSubtotal);
            $totalTax = $totalTax->plus($taxAmount);

            $detailsPayload[] = [
                'product_variant_id' => $item->productVariantId,
                'quantity' => $qty->toFloat(),
                'unit_price' => $unitPrice,
                'tax_percentage' => $item->taxPercentage,
                'tax_amount' => $taxAmount,
                'sub_total' => $lineSubtotal,
                'currency' => $data->currency,
            ];
        }

        return [
            'sub_total' => $subTotal,
            'tax_amount' => $totalTax,
            'total' => $subTotal->plus($totalTax),
            'details' => $detailsPayload,
        ];
    }
}
