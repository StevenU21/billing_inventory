<?php

namespace App\Services;

use App\DTOs\InventoryData;
use App\DTOs\InventoryMovementData;
use App\Enums\AdjustmentReason;
use App\Enums\InventoryMovementType;
use App\Exceptions\BusinessLogicException;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function processMovement(
        InventoryMovementData $data,
        ?int $userId = null,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): InventoryMovement {
        return DB::transaction(function () use ($data, $userId, $sourceType, $sourceId) {

            if ($data->inventoryId) {
                $inventory = Inventory::where('id', $data->inventoryId)->lockForUpdate()->first();
                if (!$inventory) {
                    throw new BusinessLogicException("El inventario ID {$data->inventoryId} no existe.", 'inventory_id');
                }
            } else {
                if (!$data->productVariantId) {
                    throw new BusinessLogicException('Datos insuficientes para resolver inventario.', 'inventory_params');
                }

                $inventory = Inventory::firstOrCreate(
                    [
                        'product_variant_id' => $data->productVariantId,
                    ],
                    [
                        'stock' => 0,
                        'min_stock' => 0,
                        'average_cost' => Money::zero($data->currency),
                        'currency' => $data->currency,
                    ]
                );

                $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();
            }

            if ($inventory->currency !== $data->currency) {
                throw new BusinessLogicException("Conflicto de moneda: Inventario ({$inventory->currency}) vs Movimiento ({$data->currency})", 'currency');
            }

            if (!$inventory->relationLoaded('productVariant')) {
                $inventory->load('productVariant.product.unitMeasure');
            }

            $unitMeasure = $inventory->productVariant->product->unitMeasure ?? null;
            if ($unitMeasure && !$unitMeasure->allows_decimals) {
                
                $qty = BigDecimal::of($data->quantity);
                if ($qty->remainder(1)->abs()->isGreaterThan(0)) {
                    throw new BusinessLogicException(
                        "La unidad de medida '{$unitMeasure->name}' no permite decimales. Cantidad enviada: {$data->quantity}",
                        'quantity'
                    );
                }
            }

            $currentStock = BigDecimal::of($inventory->stock);
            $multiplier = $data->type->multiplier();
            $delta = $data->quantity->multipliedBy($multiplier);
            $newStock = $currentStock->plus($delta);

            if ($newStock->isNegative() && !$this->allowsNegativeStock()) {
                $productName = $inventory->variant->product->name ?? 'Producto';
                throw new BusinessLogicException(
                    "Stock insuficiente para: {$productName}. Disponible: {$currentStock}, Solicitado: {$data->quantity}",
                    'quantity'
                );
            }

            $currentCost = $inventory->average_cost ?? Money::zero($inventory->currency);

            if ($data->type === InventoryMovementType::Purchase && $data->unitPrice === null) {
                throw new BusinessLogicException('No se puede registrar una compra sin precio unitario.', 'unit_price');
            }

            if ($multiplier > 0 && $data->unitPrice !== null) {

                $incomingUnitCost = Money::of((string) $data->unitPrice, $data->currency);
                $incomingTotal = $incomingUnitCost->multipliedBy($data->quantity, RoundingMode::HALF_UP);

                $currentTotalValue = $currentCost->multipliedBy($currentStock, RoundingMode::HALF_UP);
                $finalTotalValue = $currentTotalValue->plus($incomingTotal);

                $newAvgCost = $newStock->isZero()
                    ? Money::zero($data->currency)
                    : $finalTotalValue->dividedBy($newStock, RoundingMode::HALF_UP);

                $inventory->average_cost = $newAvgCost;

                $movementUnitPrice = $incomingUnitCost;
                $movementTotalPrice = $incomingTotal;
            } else {
                $movementUnitPrice = $currentCost;
                $movementTotalPrice = $currentCost->multipliedBy($data->quantity, RoundingMode::HALF_UP);
            }
            $stockBefore = $inventory->stock;
            $inventory->stock = (string) $newStock;
            $inventory->save();

            $finalQty = BigDecimal::of((string) $data->quantity);
            $finalTotal = $movementUnitPrice->multipliedBy($finalQty, RoundingMode::HALF_UP);

            return $inventory->inventoryMovements()->create([
                'type' => $data->type,
                'quantity' => (string) $data->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $inventory->stock,
                'unit_price' => $movementUnitPrice,
                'total_price' => $finalTotal,
                'currency' => $data->currency,
                'user_id' => $userId ?? auth()->id(),
                'sourceable_type' => $sourceType,
                'sourceable_id' => $sourceId,
                'adjustment_reason' => $data->adjustmentReason,
                'reference' => $data->reference,
                'notes' => $data->notes,
            ]);
        });
    }

    public function adjust(
        Inventory $inventory,
        string|AdjustmentReason $reason,
        float|BigDecimal $targetStock,
        ?string $notes = null,
        ?int $userId = null
    ): void {
        DB::transaction(function () use ($inventory, $reason, $targetStock, $notes, $userId) {
            $inventory = $this->getInventoryById($inventory->id, lock: true);
            $currentStock = BigDecimal::of($inventory->stock);
            $target = $targetStock instanceof BigDecimal ? $targetStock : BigDecimal::of((string) $targetStock);

            $diff = $target->minus($currentStock);
            if ($diff->isZero()) {
                return;
            }

            $type = $diff->isPositive() ? InventoryMovementType::AdjustmentIn : InventoryMovementType::AdjustmentOut;

            $data = new InventoryMovementData(
                inventoryId: $inventory->id,
                type: $type,
                quantity: $diff->abs(),
                currency: $inventory->currency,
                adjustmentReason: $reason instanceof AdjustmentReason ? $reason : AdjustmentReason::tryFrom($reason),
                notes: $notes
            );

            $this->processMovement($data, $userId ?? auth()->id(), 'User', $userId ?? auth()->id());
        });
    }

    // Helpers de lectura
    public function getInventoryById(int $id, bool $lock = false): Inventory
    {
        $query = Inventory::where('id', $id);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    private function allowsNegativeStock(): bool
    {
        return false;
    }

    public function getInventoriesForVariants(array $variantIds, bool $lock = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = Inventory::whereIn('product_variant_id', $variantIds);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    public function getInventoryForVariant(int $variantId, bool $lock = false): ?Inventory
    {
        $query = Inventory::where('product_variant_id', $variantId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function checkStock(Inventory $inventory, BigDecimal $quantity, string $itemName): void
    {
        $unitMeasure = $inventory->productVariant->product->unitMeasure ?? null;

        if (!$unitMeasure && $inventory->relationLoaded('productVariant')) {

            $inventory->loadMissing('productVariant.product.unitMeasure');
            $unitMeasure = $inventory->productVariant->product->unitMeasure ?? null;
        }

        if ($unitMeasure && !$unitMeasure->allows_decimals) {
            if ($quantity->remainder(1)->abs()->isGreaterThan(0)) {
                throw new BusinessLogicException(
                    "La unidad de medida '{$unitMeasure->name}' no permite decimales. Cantidad solicitada: {$quantity}",
                    'quantity'
                );
            }
        }

        $currentStock = BigDecimal::of($inventory->stock);

        if ($currentStock->minus($quantity)->isNegative() && !$this->allowsNegativeStock()) {
            throw new BusinessLogicException(
                "Stock insuficiente para: {$itemName}. Disponible: {$currentStock}, Solicitado: {$quantity}",
                'quantity'
            );
        }
    }

    public function createInventory(InventoryData $data, ?InventoryMovementData $initialMovement, int $userId): Inventory
    {
        return DB::transaction(function () use ($data, $initialMovement, $userId) {
            $inventory = Inventory::create([
                'product_variant_id' => $data->productVariantId,
                'min_stock' => (string) $data->minStock,
                'stock' => 0,
                'currency' => $data->currency,
            ]);

            if ($initialMovement && $initialMovement->quantity->isPositive()) {
                $movementData = new InventoryMovementData(
                    type: $initialMovement->type,
                    quantity: $initialMovement->quantity,
                    currency: $initialMovement->currency,
                    inventoryId: $inventory->id,
                    unitPrice: $initialMovement->unitPrice,
                    reference: $initialMovement->reference ?? 'Inventario Inicial',
                    notes: $initialMovement->notes ?? 'Primer registro de inventario',
                    adjustmentReason: $initialMovement->adjustmentReason
                );

                $this->processMovement($movementData, $userId, 'User', $userId);
            }

            return $inventory;
        });
    }

    public function updateInventory(Inventory $inventory, InventoryData $data, ?InventoryMovementData $movement, int $userId): Inventory
    {
        return DB::transaction(function () use ($inventory, $data, $movement, $userId) {
            $inventory->update([
                'min_stock' => (string) $data->minStock,
            ]);

            if ($movement) {
                if ($movement->inventoryId !== $inventory->id) {
                    $movement = new InventoryMovementData(
                        type: $movement->type,
                        quantity: $movement->quantity,
                        currency: $movement->currency,
                        inventoryId: $inventory->id,
                        productVariantId: $movement->productVariantId,
                        adjustmentReason: $movement->adjustmentReason,
                        unitPrice: $movement->unitPrice,
                        reference: $movement->reference,
                        notes: $movement->notes
                    );
                }

                $this->processMovement(
                    data: $movement,
                    userId: $userId,
                    sourceType: 'User',
                    sourceId: $userId
                );
            }

            return $inventory;
        });
    }
}
