<?php

namespace App\Repositories;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    protected string $coalesceDate;
    protected string $netTotalExpr;

    public function __construct()
    {
        $this->coalesceDate = 'COALESCE(sale_date, created_at)';
        $this->netTotalExpr = '(total - COALESCE(tax_amount,0))';
    }

    /**
     * Get sales grouped by hour for a specific date.
     * Used for Traffic Report.
     */
    public function getHourlySales(Carbon $date): Collection
    {
        $hourExpr = "CAST(strftime('%H', created_at) AS integer)";

        return Sale::select(
            DB::raw($hourExpr . ' as hour'),
            DB::raw('COUNT(id) as transaction_count'),
            DB::raw('SUM(' . $this->netTotalExpr . ') as total_revenue'),
            DB::raw('AVG(' . $this->netTotalExpr . ') as average_ticket')
        )
            ->whereRaw("date({$this->coalesceDate}) = ?", [$date->toDateString()])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
