<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inventory extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'stock',
        'average_cost',
        'low_stock_notified_at',
        'min_stock',
        'product_variant_id',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'low_stock_notified_at' => 'datetime',
            'stock' => 'decimal:4',
            'min_stock' => 'decimal:4',
            'average_cost' => MoneyCast::of('currency'),
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['stock', 'min_stock', 'average_cost', 'product_variant_id']);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getPurchasePriceAttribute()
    {
        return $this->average_cost;
    }

    public function getSalePriceAttribute()
    {
        return $this->productVariant->price ?? null;
    }

    public function getVariantDisplayAttribute(): string
    {
        $variant = $this->productVariant;
        if (! $variant) {
            return 'N/A';
        }

        return $variant->attributeValues->pluck('value')->join(' / ') ?: 'Estándar';
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getStockStatusLabelAttribute(): string
    {
        return $this->is_low_stock ? 'Bajo stock' : 'En stock';
    }

    public function getStockStatusColorAttribute(): string
    {
        return $this->is_low_stock
            ? 'bg-red-600 dark:bg-red-700'
            : 'bg-green-600 dark:bg-green-700';
    }

    public function getValueInWarehouseAttribute(): float
    {
        $purchasePrice = $this->purchase_price;
        if ($purchasePrice instanceof Money) {
            $purchasePrice = (float) $purchasePrice->getMinorAmount()->toInt() / 100;
        }

        return (float) $this->stock * (float) ($purchasePrice ?? 0);
    }

    public function getIncomePotentialAttribute(): float
    {
        $salePrice = $this->sale_price;
        if ($salePrice instanceof Money) {
            $salePrice = (float) $salePrice->getMinorAmount()->toInt() / 100;
        }

        return (float) $this->stock * (float) ($salePrice ?? 0);
    }

    public function getGrossProfitPerUnitAttribute(): ?Money
    {
        $salePrice = $this->sale_price;
        $purchasePrice = $this->purchase_price;

        if (! $salePrice || ! $purchasePrice) {
            return null;
        }

        if ($salePrice->getCurrency()->getCurrencyCode() !== $purchasePrice->getCurrency()->getCurrencyCode()) {
            return null;
        }

        return $salePrice->minus($purchasePrice);
    }

    public function getGrossProfitTotalAttribute(): ?Money
    {
        $grossProfitPerUnit = $this->gross_profit_per_unit;

        if (! $grossProfitPerUnit) {
            return null;
        }

        return $grossProfitPerUnit->multipliedBy($this->stock, RoundingMode::HALF_UP);
    }

    public function getFormattedStockAttribute(): string
    {
        return number_format((float) $this->stock, 2);
    }

    public function getFormattedMinStockAttribute(): string
    {
        return number_format((float) $this->min_stock, 2);
    }

    public function getFormattedMinStockIntAttribute(): string
    {
        return number_format((float) $this->min_stock, 0);
    }

    public function getFormattedValueInWarehouseAttribute(): string
    {
        return 'C$ '.number_format($this->value_in_warehouse, 2);
    }

    public function getFormattedIncomePotentialAttribute(): string
    {
        return 'C$ '.number_format($this->income_potential, 2);
    }

    public function getStockPercentageAttribute(): float
    {
        if ($this->min_stock <= 0) {
            return 100;
        }

        $percentage = ($this->stock / ($this->min_stock * 2)) * 100;

        return min(100, max(0, $percentage));
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    public function scopeByCategory($query, $categoryId)
    {
        if (empty($categoryId)) {
            return $query;
        }

        return $query->whereHas('productVariant.product', function ($q) use ($categoryId) {
            $q->whereHas('brand', function ($brandQuery) use ($categoryId) {
                $brandQuery->where('category_id', $categoryId);
            });
        });
    }

    public function scopeByBrand($query, $brandId)
    {
        if (empty($brandId)) {
            return $query;
        }

        return $query->whereHas('productVariant.product', function ($q) use ($brandId) {
            $q->where('brand_id', $brandId);
        });
    }

    public function scopeByProduct($query, $productName)
    {
        if (empty($productName)) {
            return $query;
        }

        return $query->whereHas('productVariant.product', function ($q) use ($productName) {
            $q->where('name', 'like', "%{$productName}%");
        });
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->whereHas('productVariant', function ($q) use ($search) {
            $q->where('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
        });
    }

    public function scopeStockLevel($query, $level)
    {
        return match ($level) {
            'out_of_stock' => $query->where('stock', '<=', 0),
            'low_stock' => $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0),
            'in_stock' => $query->where('stock', '>', 0)->whereColumn('stock', '>', 'min_stock'),
            default => $query,
        };
    }

    public function scopeByTax($query, $taxId)
    {
        if (empty($taxId)) {
            return $query;
        }

        return $query->whereHas('productVariant.product', function ($q) use ($taxId) {
            $q->where('tax_id', $taxId);
        });
    }

    // =========================================================================
    // ACCESSORS & HELPERS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $product = $this->productVariant->product ?? null;
        $productName = $product->name ?? 'Producto Desconocido';

        $variantInfo = '';
        if ($this->productVariant) {
            $attributes = $this->productVariant->attributeValues->pluck('value')->join(' / ');
            if (! empty($attributes)) {
                $variantInfo = ' ('.$attributes.')';
            }
        }

        return "{$productName}{$variantInfo}";
    }
}
