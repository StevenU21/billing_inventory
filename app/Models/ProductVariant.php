<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property-read Collection|ProductAttributeValue[] $attributeValues
 */
class ProductVariant extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    public const SUPPORTED_CURRENCIES = ['NIO', 'USD'];

    protected $fillable = [
        'product_id',
        'search_text',
        'sku',
        'barcode',
        'price',
        'cost',
        'conversion_factor',
        'credit_price',
        'image',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::of('currency'),
            'cost' => MoneyCast::of('currency'),
            'conversion_factor' => 'decimal:4',
            'credit_price' => MoneyCast::of('currency'),
            'product_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'sku',
                'barcode',
                'price',
                'cost',
                'credit_price',
                'product_id',
                'image',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function attributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function quotationDetails()
    {
        return $this->hasMany(QuotationDetail::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function getHasCommercialMovementsAttribute(): bool
    {
        if ($this->relationLoaded('purchaseDetails') && $this->purchaseDetails->isNotEmpty()) {
            return true;
        }

        if ($this->relationLoaded('saleDetails') && $this->saleDetails->isNotEmpty()) {
            return true;
        }

        return $this->purchaseDetails()->exists() || $this->saleDetails()->exists();
    }

    public function getAttributesLockedAttribute(): bool
    {
        return $this->has_commercial_movements;
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return route('product.variant.image', $this->id);
        }

        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->image_url;
        }

        return asset('img/image04.png');
    }

    public function getHasRealImageAttribute(): bool
    {
        if ($this->image) {
            return true;
        }

        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->has_real_image;
        }

        return false;
    }

    public function getStockQuantityAttribute(): float
    {
        return (float) $this->inventories->sum('stock');
    }

    public function getCurrentUnitPriceAttribute(): float
    {
        if ($this->price instanceof Money) {
            return $this->price->getAmount()->toFloat();
        }

        return (float) ($this->price ?? 0);
    }

    public function getTotalValueAttribute(): float
    {
        return round($this->current_unit_price * $this->stock_quantity, 2);
    }

    public function getCurrentUnitPriceMoneyAttribute(): ?Money
    {
        return $this->price;
    }

    public function getTotalValueMoneyAttribute(): ?Money
    {
        if (! $this->price) {
            return null;
        }

        return $this->price->multipliedBy($this->stock_quantity, RoundingMode::HALF_UP);
    }

    public function getFormattedCurrentUnitPriceAttribute(): string
    {
        $price = $this->current_unit_price_money;

        if ($price) {
            return $price->formatTo('es_NI');
        }

        return 'C$ '.number_format($this->current_unit_price, 2, '.', ',');
    }

    public function getFormattedTotalValueAttribute(): string
    {
        $total = $this->total_value_money;

        if ($total) {
            return $total->formatTo('es_NI');
        }

        return 'C$ '.number_format($this->total_value, 2, '.', ',');
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stock_quantity;
    }

    public function getCalculatedPriceAttribute(): float
    {
        return $this->current_unit_price;
    }

    public function getAuditDisplayAttribute(): string
    {
        // Cargar relaciones si no están cargadas (defensive programming)
        if (! $this->relationLoaded('product')) {
            $this->load('product');
        }

        if (! $this->relationLoaded('attributeValues')) {
            $this->load('attributeValues.attribute');
        }

        $productName = $this->product?->name ?? 'Producto';

        $dimensions = [];

        if ($this->attributeValues->isNotEmpty()) {
            foreach ($this->attributeValues as $attributeValue) {
                if ($attributeValue->attribute) {
                    $dimensions[] = "{$attributeValue->attribute->name}: {$attributeValue->value}";
                }
            }
        }

        if (! empty($dimensions)) {
            return "{$productName} (".implode(', ', $dimensions).')';
        }

        return $productName;
    }
}
