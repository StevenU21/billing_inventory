<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseDetail extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'quantity',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'currency',
        'purchase_id',
        'product_variant_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'tax_percentage' => 'decimal:2',
            'unit_price' => MoneyCast::of('currency'),
            'tax_amount' => MoneyCast::of('currency'),
            'purchase_id' => 'integer',
            'product_variant_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'quantity',
                'unit_price',
                'purchase_id',
                'product_variant_id',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedUnitPriceAttribute(): string
    {
        return $this->formatMoney($this->unit_price);
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format((float) $this->quantity, 2, '.', ',');
    }

    public function getFormattedTaxAttribute(): string
    {
        return $this->formatMoney($this->tax_amount);
    }

    public function getFormattedAmountAttribute(): string
    {
        if (! $this->unit_price) {
            return $this->formatMoney(null);
        }

        $amount = $this->unit_price->multipliedBy((string) $this->quantity);

        return $this->formatMoney($amount);
    }

    public function getFormattedTotalAttribute(): string
    {
        if (! $this->unit_price) {
            return $this->formatMoney(null);
        }

        $subtotal = $this->unit_price->multipliedBy((string) $this->quantity);
        $total = $subtotal->plus($this->tax_amount ?? \Brick\Money\Money::zero($this->currency ?? 'NIO'));

        return $this->formatMoney($total);
    }

    public function getProductNameAttribute(): string
    {
        return $this->productVariant?->product?->name ?? 'Producto desconocido';
    }

    public function getVariantDisplayAttribute(): string
    {
        $variant = $this->productVariant;
        if (! $variant) {
            return 'Simple';
        }

        $options = $variant->attributeValues->pluck('value')->toArray();

        return empty($options) ? 'Simple' : implode(' / ', $options);
    }

    public function getAuditDisplayAttribute(): string
    {
        $productName = 'Producto desconocido';
        $variantDetails = '';

        if ($this->productVariant) {
            if ($this->productVariant->product) {
                $productName = $this->productVariant->product->name;
            }

            $details = $this->productVariant->attributeValues->pluck('value')->toArray();

            if (! empty($details)) {
                $variantDetails = '('.implode(', ', $details).')';
            }
        }

        return "Detalle Compra #{$this->id} - Compra #{$this->purchase_id} - {$productName} {$variantDetails}";
    }
}
