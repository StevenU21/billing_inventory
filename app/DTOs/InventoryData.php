<?php

namespace App\DTOs;

use Brick\Math\BigDecimal;

readonly class InventoryData
{
    public function __construct(
        public int $productVariantId,
        public BigDecimal $stock,
        public BigDecimal $minStock,
        public string $currency,
        public ?int $id = null,
    ) {
    }

    /**
     * @param array<string, mixed> $validated
     * @param int|null $id
     * @return self
     */
    public static function fromRequest(array $validated, ?int $id = null): self
    {
        return new self(
            productVariantId: (int) $validated['product_variant_id'],
            stock: BigDecimal::of((string) ($validated['stock'] ?? '0')),
            minStock: BigDecimal::of((string) ($validated['min_stock'] ?? '0')),
            currency: $validated['currency'],
            id: $id,
        );
    }
}
