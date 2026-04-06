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
        public array $items,
        public ProductStatus $status,
        public ?string $description = null,
        public array $attributes = [],
        public ?string $code = null,
        public ?UploadedFile $image = null,
        public ?int $id = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        $items = array_map(
            fn ($item) => ProductVariantData::fromArray($item),
            $validated['variants'] ?? []
        );

        return new self(
            name: $validated['name'],
            brandId: (int) $validated['brand_id'],
            taxId: (int) $validated['tax_id'],
            unitMeasureId: (int) $validated['unit_measure_id'],
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
