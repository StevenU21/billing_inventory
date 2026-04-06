<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountPayablePaymentRequest;
use App\DTOs\AccountPayablePaymentData;
use App\Models\AccountPayable;
use App\Services\AccountPayablePaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class AccountPayablePaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AccountPayablePaymentService $service
    ) {
    }

    public function store(AccountPayablePaymentRequest $request): RedirectResponse
    {
        $accountPayable = AccountPayable::findOrFail($request->validated('account_payable_id'));

        $this->authorize('create', $accountPayable); // O Policy específica para pagos

        $data = AccountPayablePaymentData::fromRequest(
            $request->validated() + ['user_id' => $request->user()->id],
            $accountPayable
        );

        $this->service->createPayment($data);

        return back()->with('success', 'Pago registrado exitosamente.');
    }
}
