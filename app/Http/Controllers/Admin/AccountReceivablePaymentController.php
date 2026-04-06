<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\AccountReceivablePaymentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccountReceivablePaymentRequest;
use App\Models\AccountReceivable;
use App\Services\AccountReceivablePaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class AccountReceivablePaymentController extends Controller
{
    use AuthorizesRequests;

    public function store(AccountReceivablePaymentRequest $request, AccountReceivablePaymentService $service, AccountReceivable $accountReceivable)
    {
        $service->createPayment(
            AccountReceivablePaymentData::fromRequest($request->validated() + ['user_id' => Auth::id()], $accountReceivable)
        );

        return back()->with('success', __('Pago registrado correctamente.'));
    }
}

