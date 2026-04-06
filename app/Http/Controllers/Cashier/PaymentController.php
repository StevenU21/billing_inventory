<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\AccountReceivablePayment;
use App\Services\PaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function store(PaymentRequest $request, PaymentService $paymentService): JsonResponse
    {
        $this->authorize('create', AccountReceivablePayment::class);

        $result = $paymentService->createPayment($request->validated());

        return response()->json([
            'message' => 'Pago registrado correctamente.',
            'data' => [
                'payment' => $result['payment'],
                'account_receivable' => $result['accountReceivable'],
            ],
        ]);
    }
}
