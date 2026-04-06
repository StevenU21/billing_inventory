<?php

namespace App\Models;

use App\Enums\AdjustmentReason;
use App\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasFormattedTimestamps;
use App\Traits\HasFormattedMoney;
use Elegantly\Money\MoneyCast;

class InventoryMovement extends Model
{
    use HasFormattedTimestamps, HasFactory, HasFormattedMoney;
    protected $fillable = [
        'type',
        'adjustment_reason',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_price',
        'total_price',
        'currency',
        'notes',
        'user_id',
        'inventory_id',
        'sourceable_id',
        'sourceable_type'
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryMovementType::class,
            'adjustment_reason' => AdjustmentReason::class,
            'quantity' => 'decimal:4',
            'stock_before' => 'decimal:4',
            'stock_after' => 'decimal:4',
            'unit_price' => MoneyCast::of('currency'),
            'total_price' => MoneyCast::of('currency'),
            'user_id' => 'integer',
            'inventory_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'adjustment_reason', 'quantity', 'unit_price', 'total_price', 'notes', 'user_id', 'inventory_id']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function sourceable()
    {
        return $this->morphTo();
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getMovementTypeAttribute(): string
    {
        return $this->type->label();
    }

    public function getMovementTypeColorAttribute(): string
    {
        return $this->type->color();
    }

    public function getMovementTypeBadgeAttribute(): string
    {
        return $this->type->badgeColor();
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format((float) $this->quantity, 2);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return $this->formatMoney($this->unit_price);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->formatMoney($this->total_price);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $productName = $this->inventory?->productVariant?->product?->name ?? 'Producto desconocido';
        return "Movimiento #{$this->id} - {$this->movement_type} - {$productName}";
    }
}
