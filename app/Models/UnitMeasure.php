<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Traits\HasFormattedTimestamps;

class UnitMeasure extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;
    protected $fillable = [
        'name',
        'symbol',
        'allows_decimals',
    ];

    protected function casts(): array
    {
        return [
            'allows_decimals' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'symbol', 'allows_decimals']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('symbol', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
