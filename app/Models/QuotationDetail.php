<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Elegantly\Money\MoneyCast;

class QuotationDetail extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;

    protected $fillable = [
        'quantity',
        'unit_price',
        'discount',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'sub_total',
        'currency',
        'product_variant_id',
        'quotation_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => MoneyCast::of('currency'),
            'discount' => 'boolean',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => MoneyCast::of('currency'),
            'tax_percentage' => 'decimal:2',
            'tax_amount' => MoneyCast::of('currency'),
            'sub_total' => MoneyCast::of('currency'),
            'product_variant_id' => 'integer',
            'quotation_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'quantity',
                'unit_price',
                'discount',
                'discount_amount',
                'sub_total',
                'product_variant_id',
                'quotation_id',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $productName = $this->productVariant?->product?->name ?? 'Producto desconocido';
        return "Detalle Cotización #{$this->quotation_id} - {$productName}";
    }
}
