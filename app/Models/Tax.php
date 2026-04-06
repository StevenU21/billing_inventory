<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Traits\HasFormattedTimestamps;

class Tax extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;
    protected $fillable = [
        'name',
        'percentage'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'percentage']);
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
        return $query->where('name', 'like', "%{$search}%");
    }
}
