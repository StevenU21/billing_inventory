<?php

namespace App\DTOs;

use Brick\Math\BigDecimal;

readonly class PurchaseItemData
{
    public function __construct(
        public int $productVariantId,
        public BigDecimal $quantity,
        public BigDecimal $unitPrice,
        public BigDecimal $taxPercentage,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productVariantId: (int) $data['product_variant_id'],
            quantity: BigDecimal::of((string) $data['quantity']),
            unitPrice: BigDecimal::of((string) $data['unit_price']),
            taxPercentage: BigDecimal::of((string) $data['tax_percentage']),
        );
    }
}
