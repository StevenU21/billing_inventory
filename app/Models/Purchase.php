<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use App\Enums\PurchaseType;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Purchase extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'status',
        'is_credit',
        'reference',
        'tax_amount',
        'sub_total',
        'total',
        'purchase_date',
        'currency',
        'supplier_id',

        'user_id',
        'payment_method_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseStatus::class,
            'is_credit' => 'boolean',
            'purchase_date' => 'immutable_datetime',
            'received_at' => 'datetime',
            'tax_amount' => MoneyCast::of('currency'),
            'sub_total' => MoneyCast::of('currency'),
            'total' => MoneyCast::of('currency'),
            'supplier_id' => 'integer',

            'user_id' => 'integer',
            'payment_method_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'reference',
                'is_credit',
                'sub_total',
                'total',
                'supplier_id',
                'user_id',
                'payment_method_id',
            ]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function getAuditDisplayAttribute(): string
    {
        $entityName = $this->entity ? $this->entity->full_name : 'N/A';

        return "Compra #{$this->id} - Ref: {$this->reference} - Proveedor: {$entityName} - Total: {$this->total}";
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getSummaryAttribute(): string
    {
        $details = $this->details;
        if ($details->isEmpty()) {
            return 'Sin ítems';
        }

        $aggregates = [];
        foreach ($details as $detail) {
            $name = $detail->productVariant?->product?->name ?? 'Ítem eliminado';
            $qty = (float) ($detail->quantity ?? 1);
            $aggregates[$name] = ($aggregates[$name] ?? 0) + $qty;
        }

        $tokens = collect($aggregates)->map(function ($qty, $name) {
            $rounded = (int) round($qty);

            return $rounded > 1 ? ($rounded . 'x ' . $name) : $name;
        })->values();

        $typesCount = $tokens->count();
        if ($typesCount === 1) {
            return $tokens->first();
        }
        if ($typesCount <= 3) {
            return $tokens->join(', ', ' y ');
        }

        return $tokens->first() . ' y ' . ($typesCount - 1) . ' más...';
    }

    public function getPurchaseTypeAttribute(): PurchaseType
    {
        return PurchaseType::fromBoolean($this->is_credit);
    }

    public function getDetailsCountAttribute(): int
    {
        return $this->details->count();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeSearch($query, $value)
    {
        return $query->where(function ($q) use ($value) {
            $q->where('id', $value)
                ->orWhere('reference', 'like', "%{$value}%")
                ->orWhereHas('entity', function ($qe) use ($value) {
                    $qe->where(DB::raw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,''))"), 'like', '%' . $value . '%')
                        ->orWhere('short_name', 'like', "%{$value}%");
                });
        });
    }
}
