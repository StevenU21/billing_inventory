<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductAttributeValue extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_attribute_id',
        'value',
        'abbreviation',
    ];

    protected function casts(): array
    {
        return [
            'product_attribute_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_attribute_id',
                'value',
                'abbreviation',
            ])
            ->logOnlyDirty();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_values');
    }
}
