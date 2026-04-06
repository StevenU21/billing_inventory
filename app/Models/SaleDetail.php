<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Elegantly\Money\MoneyCast;
use Brick\Money\Money;
use Brick\Math\RoundingMode;

class SaleDetail extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps, HasFormattedMoney;

    protected $fillable = [
        'quantity',
        'unit_price',
        'unit_cost',
        'sub_total',
        'conversion_factor_applied',
        'discount',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'currency',
        'product_variant_id',
        'sale_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => MoneyCast::of('currency'),
            'unit_cost' => MoneyCast::of('currency'),
            'sub_total' => MoneyCast::of('currency'),
            'conversion_factor_applied' => 'decimal:4',
            'discount' => 'boolean',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => MoneyCast::of('currency'),
            'tax_amount' => MoneyCast::of('currency'),
            'tax_percentage' => 'decimal:2',
            'product_variant_id' => 'integer',
            'sale_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'quantity',
                'unit_price',
                'sub_total',
                'discount',
                'discount_amount',
                'product_variant_id',
                'sale_id',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $productName = $this->productVariant?->product?->name ?? 'Producto desconocido';
        return "Detalle Venta #{$this->sale_id} - {$productName}";
    }

    public function getTaxAmountAttribute(): Money
    {
        $taxPercentage = $this->productVariant?->product?->tax?->percentage ?? 0;
        $baseAmount = $this->sub_total ? $this->sub_total->minus($this->discount_amount ?? Money::zero('NIO')) : Money::zero('NIO');
        return $baseAmount->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);
    }

    public function getTotalAttribute(): Money
    {
        $net = $this->sub_total ? $this->sub_total->minus($this->discount_amount ?? Money::zero('NIO')) : Money::zero('NIO');
        return $net->plus($this->tax_amount);
    }

    public function getSubtotalAttribute(): Money
    {
        return $this->sub_total ?? $this->unit_price->multipliedBy($this->quantity, RoundingMode::HALF_UP);
    }

    public function getProductNameAttribute(): string
    {
        return $this->productVariant?->product?->name ?? 'Producto desconocido';
    }

    public function getVariantDisplayAttribute(): string
    {
        $variant = $this->productVariant;
        if (!$variant) {
            return 'Simple';
        }

        $options = $variant->attributeValues->pluck('value')->toArray();

        return empty($options) ? 'Simple' : implode(' / ', $options);
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format((float) $this->quantity, 2, '.', ',');
    }
}
