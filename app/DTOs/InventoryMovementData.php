<?php

namespace App\DTOs;

use App\Enums\AdjustmentReason;
use App\Enums\InventoryMovementType;
use Brick\Math\BigDecimal;

readonly class InventoryMovementData
{
    public function __construct(
        public InventoryMovementType $type,
        public BigDecimal $quantity,
        public string $currency,
        public ?int $inventoryId = null,
        public ?int $productVariantId = null,
        public ?AdjustmentReason $adjustmentReason = null,
        public ?BigDecimal $unitPrice = null,
        public ?string $reference = null,
        public ?string $notes = null,
    ) {
    }

    public static function fromRequest(array $validated): self
    {
        return new self(
            type: InventoryMovementType::from($validated['movement_type'] ?? $validated['type']),
            quantity: BigDecimal::of((string) $validated['quantity']),
            currency: $validated['currency'],
            inventoryId: isset($validated['inventory_id']) ? (int) $validated['inventory_id'] : null,
            productVariantId: isset($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null,
            adjustmentReason: isset($validated['adjustment_reason'])
            ? AdjustmentReason::from($validated['adjustment_reason'])
            : null,
            unitPrice: isset($validated['unit_price'])
            ? BigDecimal::of((string) $validated['unit_price'])
            : null,
            reference: $validated['reference'] ?? null,
            notes: $validated['notes'] ?? null,
        );
    }
}
