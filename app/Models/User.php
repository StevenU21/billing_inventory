<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

use App\Traits\HasFormattedTimestamps;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, HasFormattedTimestamps;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'is_active']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class);
    }

    public function priceHistoryChanges()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedRoleNameAttribute(): ?string
    {
        $roleName = $this->roles->pluck('name')->first();
        if ($roleName === 'admin') {
            return 'ADMINISTRADOR';
        } elseif ($roleName === 'cashier') {
            return 'CAJERO';
        } elseif ($roleName) {
            return mb_strtoupper($roleName);
        }
        return null;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getShortNameAttribute(): string
    {
        $first = explode(' ', trim($this->first_name))[0] ?? '';
        $last = explode(' ', trim($this->last_name))[0] ?? '';
        return trim($first . ' ' . $last);
    }
}
