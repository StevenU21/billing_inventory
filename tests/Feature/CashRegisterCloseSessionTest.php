<?php

namespace Tests\Feature;

use App\DTOs\CashSessionCloseData;
use App\Enums\CashRegisterSessionStatus;
use App\Http\Requests\CloseCashSessionRequest;
use App\Models\CashRegisterSession;
use App\Models\User;
use App\Services\CashRegisterService;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CashRegisterCloseSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_session_request_requires_notes_when_balance_differs(): void
    {
        $session = new CashRegisterSession;
        $session->forceFill([
            'currency' => 'NIO',
            'expected_closing_balance' => Money::of('100.00', 'NIO'),
        ]);

        $request = CloseCashSessionRequest::create('/admin/cash-register/1/close', 'POST', [
            'actual_closing_balance' => '90.00',
            'currency' => 'NIO',
            'notes' => '',
        ]);

        $request->setRouteResolver(function () use ($session) {
            return new class($session)
            {
                public function __construct(private CashRegisterSession $session) {}

                public function parameter(string $key): mixed
                {
                    return $key === 'session' ? $this->session : null;
                }
            };
        });

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        $this->assertTrue($validator->errors()->has('notes'));
    }

    public function test_close_session_service_persists_notes_and_difference(): void
    {
        $user = User::factory()->create();

        $session = CashRegisterSession::create([
            'user_id' => $user->id,
            'opened_by' => $user->id,
            'opening_balance' => Money::of('100.00', 'NIO'),
            'expected_closing_balance' => Money::of('100.00', 'NIO'),
            'status' => CashRegisterSessionStatus::Open,
            'opened_at' => now(),
            'currency' => 'NIO',
        ]);

        $service = app(CashRegisterService::class);

        $closedSession = $service->closeSession(new CashSessionCloseData(
            sessionId: $session->id,
            actualClosingBalance: Money::of('90.00', 'NIO'),
            closedByUserId: $user->id,
            notes: 'Faltó efectivo por un retiro registrado tarde',
        ));

        $this->assertSame('Faltó efectivo por un retiro registrado tarde', $closedSession->notes);
        $this->assertTrue($closedSession->has_difference);
        $this->assertSame('faltante', $closedSession->difference_type);
        $this->assertTrue($closedSession->difference->isEqualTo(Money::of('-10.00', 'NIO')));
        $this->assertSame(CashRegisterSessionStatus::Closed, $closedSession->status);
    }
}
