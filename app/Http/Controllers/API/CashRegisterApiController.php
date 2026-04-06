<?php

namespace App\Http\Controllers\API;

use App\Enums\CashRegisterSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\CashRegisterSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashRegisterApiController extends Controller
{
    /**
     * Get the last closing balance for the authenticated user.
     */
    public function getLastClosingBalance(Request $request): JsonResponse
    {
        $lastSession = CashRegisterSession::where('user_id', $request->user()->id)
            ->where('status', CashRegisterSessionStatus::Closed)
            ->latest('closed_at')
            ->first();

        if (! $lastSession) {
            return response()->json([
                'amount' => 0,
                'formatted' => 'C$ 0.00',
            ]);
        }

        // We use the ACTUAL closing balance as the opening balance for the next session
        return response()->json([
            'amount' => $lastSession->actual_closing_balance->getAmount()->toString(),
            'formatted' => $lastSession->formatted_actual_closing_balance,
        ]);
    }
}
