<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Elegantly\Money\MoneyCast;

class ProductPriceHistory extends Model
{
    use LogsActivity, HasFormattedTimestamps;

    protected $table = 'product_price_history';

    protected $fillable = [
        'product_variant_id',
        'user_id',
        'old_price',
        'new_price',
        'old_cost',
        'new_cost',
        'notes',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'old_price' => MoneyCast::of('currency'),
            'new_price' => MoneyCast::of('currency'),
            'old_cost' => MoneyCast::of('currency'),
            'new_cost' => MoneyCast::of('currency'),
            'product_variant_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_variant_id',
                'old_price',
                'new_price',
                'old_cost',
                'new_cost',
                'notes',
            ])
            ->logOnlyDirty();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $productName = $this->productVariant?->product?->name ?? 'Producto desconocido';
        return "Cambio de precio - {$productName}";
    }
}
