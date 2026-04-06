<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductAttribute extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'slug',
            ])
            ->logOnlyDirty();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        return "Atributo de Producto - {$this->name}";
    }
}
