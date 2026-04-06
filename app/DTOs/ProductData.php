<?php

namespace App\DTOs;

use App\Enums\ProductStatus;
use Illuminate\Http\UploadedFile;

readonly class ProductData
{
    /**
     * @param  ProductVariantData[]  $items
     */
    public function __construct(
        public string $name,
        public int $brandId,
        public int $taxId,
        public int $unitMeasureId,
        public string $currency,
        public array $items,
        public ProductStatus $status,
        public ?string $description,
        public array $attributes,
        public ?string $code,
        public ?UploadedFile $image,
        public ?int $id,
    ) {}

    public static function fromRequest(array $validated): self
    {
        $currency = $validated['currency'] ?? 'NIO';

        $items = array_map(
            fn ($item) => ProductVariantData::fromArray($item, $currency),
            $validated['variants'] ?? []
        );

        return new self(
            name: $validated['name'],
            brandId: (int) $validated['brand_id'],
            taxId: (int) $validated['tax_id'],
            unitMeasureId: (int) $validated['unit_measure_id'],
            currency: $currency,
            items: $items,
            status: ProductStatus::tryFrom($validated['status'] ?? '') ?? ProductStatus::Draft,
            description: $validated['description'] ?? null,
            attributes: $validated['attributes'] ?? [],
            code: $validated['code'] ?? null,
            image: $validated['image'] ?? null,
            id: isset($validated['id']) ? (int) $validated['id'] : null,
        );
    }
}
