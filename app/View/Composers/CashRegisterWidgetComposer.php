<?php

namespace App\View\Composers;

use App\Enums\CashRegisterSessionStatus;
use App\Models\CashRegisterSession;
use Illuminate\View\View;

class CashRegisterWidgetComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $activeSession = null;

        if (auth()->check()) {
            $activeSession = CashRegisterSession::where('user_id', auth()->id())
                ->where('status', CashRegisterSessionStatus::Open)
                ->first();
        }

        $view->with([
            'activeSession' => $activeSession,
            'hasSession' => $activeSession !== null,
            'sessionId' => $activeSession?->id,
            'expectedBalance' => $activeSession?->formatted_current_expected_balance ?? 'C$ 0.00',
            'totalIncome' => $activeSession?->formatted_total_income ?? 'C$ 0.00',
            'totalExpense' => $activeSession?->formatted_total_expense ?? 'C$ 0.00',
            'showUrl' => $activeSession ? route('admin.cash-register.show', $activeSession) : '#',
            'closeUrl' => $activeSession ? route('admin.cash-register.close-form', $activeSession) : '#',
        ]);
    }
}
