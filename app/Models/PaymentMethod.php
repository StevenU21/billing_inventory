<?php

namespace App\Models;

use App\Traits\HasFormattedTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
{
    use HasFactory, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'is_cash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_cash' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'is_cash', 'is_active']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class);
    }

    public function cashRegisterMovements()
    {
        return $this->hasMany(CashRegisterMovement::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCash($query)
    {
        return $query->where('is_cash', true);
    }
}
