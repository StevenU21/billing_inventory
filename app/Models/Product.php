<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'code',
        'image',
        'status',
        'brand_id',
        'tax_id',
        'unit_measure_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'brand_id' => 'integer',
            'tax_id' => 'integer',
            'unit_measure_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'description',
                'code',
                'status',
                'brand_id',
                'tax_id',
                'unit_measure_id',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function unitMeasure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedStatusAttribute(): array
    {
        return [
            'label' => $this->status->label(),
            'color' => $this->status->color(),
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? route('product.image', $this)
            : asset('img/image04.png');
    }

    public function getHasRealImageAttribute(): bool
    {
        return $this->image && ! str_contains($this->image_url, 'image04.png');
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr($this->name ?? '', 0, 2));
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isEditable(): bool
    {
        return $this->status === ProductStatus::Available || $this->status === ProductStatus::Draft;
    }

    public function isArchived(): bool
    {
        return $this->status === ProductStatus::Archived;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================
    public function scopeCategoryId($query, $categoryId)
    {
        return $query->whereHas('brand', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('variants', function ($variantQuery) use ($search) {
                    $variantQuery->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
        });
    }

    public function getVariantBadgesAttribute(): array
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->with('attributeValues.attribute')->get();

        if ($variants->isEmpty()) {
            return [
                'has_options' => false,
                'total_count' => 0,
                'attributes' => [],
            ];
        }

        $attributeMap = [];

        foreach ($variants as $variant) {
            $values = $variant->relationLoaded('attributeValues') ? $variant->attributeValues : $variant->attributeValues()->with('attribute')->get();

            foreach ($values as $av) {
                $attrName = $av->attribute->name;
                $val = $av->value;

                if (! isset($attributeMap[$attrName])) {
                    $attributeMap[$attrName] = [];
                }
                // Avoid duplicates
                if (! in_array($val, $attributeMap[$attrName])) {
                    $attributeMap[$attrName][] = $val;
                }
            }
        }

        $attributesData = [];
        foreach ($attributeMap as $name => $values) {
            $show = array_slice($values, 0, 4);
            $overflow = count($values) - count($show);

            $attributesData[$name] = [
                'badges' => $show,
                'overflow' => $overflow,
            ];
        }

        return [
            'has_options' => ! empty($attributesData),
            'total_count' => $variants->count(),
            'attributes' => $attributesData,
        ];
    }
}
