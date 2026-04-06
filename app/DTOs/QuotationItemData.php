<?php

namespace App\DTOs;

use Brick\Math\BigDecimal;

readonly class QuotationItemData
{
    public function __construct(
        public int $productVariantId,
        public BigDecimal $quantity,
        public bool $discount = false,
        public ?BigDecimal $discountPercentage = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productVariantId: (int) $data['product_variant_id'],
            quantity: BigDecimal::of((string) $data['quantity']),
            discount: (bool) ($data['discount'] ?? false),
            discountPercentage: isset($data['discount_percentage'])
            ? BigDecimal::of((string) $data['discount_percentage'])
            : null,
        );
    }
}
