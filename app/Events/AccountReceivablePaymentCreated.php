<?php

namespace App\Events;

use App\Models\AccountReceivablePayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountReceivablePaymentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AccountReceivablePayment $payment
    ) {
    }
}