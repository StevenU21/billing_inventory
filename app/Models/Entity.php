<?php

namespace App\Models;

use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

// Removed heavy financial logic in favor of EntityFinancialService

class Entity extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'first_name',
        'last_name',
        'identity_card',
        'ruc',
        'email',
        'phone',
        'address',
        'description',
        'is_client',
        'is_supplier',
        'is_active',
        'municipality_id',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'is_supplier' => 'boolean',
        'is_active' => 'boolean',
        'municipality_id' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'client_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function accountReceivables()
    {
        return $this->hasMany(AccountReceivable::class, 'client_id');
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class, 'client_id');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getShortNameAttribute(): string
    {
        return $this->full_name;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $raw = preg_replace('/\D/', '', $this->phone);

        // Nicaragua default formatting: +505 XXXX-XXXX
        if (strlen($raw) === 8) {
            $first = substr($raw, 0, 4);
            $second = substr($raw, 4, 4);

            return '+505 ' . $first . '-' . $second;
        }

        if (strlen($raw) === 11 && str_starts_with($raw, '505')) {
            $local = substr($raw, -8);
            $first = substr($local, 0, 4);
            $second = substr($local, 4, 4);

            return '+505 ' . $first . '-' . $second;
        }

        return $this->phone;
    }

    public function getFormattedIdentityCardAttribute(): ?string
    {
        if (!$this->identity_card) {
            return null;
        }

        $raw = preg_replace('/[^A-Za-z0-9]/', '', $this->identity_card);

        if (preg_match('/^(\d{3})(\d{6})(\d{4})([A-Za-z]?)$/', $raw, $m)) {
            $formatted = $m[1] . '-' . $m[2] . '-' . $m[3];
            if (!empty($m[4])) {
                $formatted .= strtoupper($m[4]);
            }

            return $formatted;
        }

        return $this->identity_card;
    }

    public function getFormattedLocationAttribute(): array
    {
        if (!$this->municipality) {
            return [
                'municipality' => null,
                'department' => null,
                'show_department' => false,
            ];
        }

        $municipalityName = $this->municipality->name;
        $departmentName = $this->municipality->department?->name;

        // Only show department if it differs from municipality name
        $showDepartment = $departmentName && $municipalityName !== $departmentName;

        return [
            'municipality' => $municipalityName,
            'department' => $departmentName,
            'show_department' => $showDepartment,
        ];
    }

    public function getFormattedIdentificationAttribute(): array
    {
        $hasRuc = !empty($this->ruc);
        $hasCedula = !empty($this->formatted_identity_card);
        $isCompany = $hasRuc;

        if ($isCompany) {
            return [
                'primary' => $this->ruc,
                'secondary' => $hasCedula ? $this->formatted_identity_card : null,
                'is_company' => true,
            ];
        }

        return [
            'primary' => $hasCedula ? $this->formatted_identity_card : null,
            'secondary' => null,
            'is_company' => false,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'identity_card', 'ruc', 'email', 'phone', 'address', 'description', 'is_client', 'is_supplier', 'is_active']);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeActiveClients($query)
    {
        return $query->where('is_client', true)->where('is_active', true);
    }

    public function scopeVisibleFor(Builder $query, Authenticatable $user): Builder
    {
        $gate = Gate::forUser($user);
        $canReadClients = $gate->allows('read clients');
        $canReadSuppliers = $gate->allows('read suppliers');

        if ($canReadClients && !$canReadSuppliers) {
            return $query->where('is_client', true);
        }

        if ($canReadSuppliers && !$canReadClients) {
            return $query->where('is_supplier', true);
        }

        if (!$canReadClients && !$canReadSuppliers) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
}
