<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

class Audit extends Activity
{
    public function scopeRange(Builder $query, string $range)
    {
        if ($range === 'hoy') {
            return $query->whereDate('created_at', now()->toDateString());
        } elseif ($range === 'semana') {
            return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($range === 'mes') {
            return $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($range === 'historico') {
            // No filter
            return $query;
        }
        return $query;
    }
}
