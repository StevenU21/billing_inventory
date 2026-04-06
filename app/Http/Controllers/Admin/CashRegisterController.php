<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CashMovementData;
use App\DTOs\CashSessionCloseData;
use App\DTOs\CashSessionOpenData;
use App\Enums\CashRegisterSessionStatus;
use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashMovementRequest;
use App\Http\Requests\CloseCashSessionRequest;
use App\Http\Requests\OpenCashSessionRequest;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use App\Services\CashRegisterService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CashRegisterController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    /**
     * Display a listing of cash register sessions.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CashRegisterSession::class);

        $sessions = QueryBuilder::for(CashRegisterSession::class)
            ->allowedFilters(...[
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('from', function ($query, $value) {
                    $query->where('opened_at', '>=', Carbon::parse($value)->startOfDay());
                }),
                AllowedFilter::callback('to', function ($query, $value) {
                    $query->where('opened_at', '<=', Carbon::parse($value)->endOfDay());
                }),
            ])
            ->allowedSorts(...['id', 'opened_at', 'closed_at', 'opening_balance'])
            ->defaultSort('-id')
            ->with(['user:id,first_name,last_name', 'openedByUser:id,first_name,last_name', 'closedByUser:id,first_name,last_name'])
            ->withCount('movements')
            ->paginate(10)
            ->withQueryString();

        return view('admin.cash-register.index', compact('sessions'));
    }

    /**
     * Show the form for opening a new session.
     */
    public function create()
    {
        $this->authorize('open', CashRegisterSession::class);

        $existingSession = $this->cashRegisterService->getAnyOpenSession();

        if ($existingSession) {
            return redirect()
                ->route('admin.cash-register.index')
                ->with('warning', 'Ya existe una sesión de caja abierta. Debe cerrarse antes de abrir otra.');
        }

        return view('admin.cash-register.create');
    }

    /**
     * Open a new cash register session.
     */
    public function store(OpenCashSessionRequest $request)
    {
        try {
            $data = CashSessionOpenData::fromRequest(
                $request->validated() + ['user_id' => $request->user()->id]
            );

            $session = $this->cashRegisterService->openSession($data);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Sesión de caja abierta correctamente.',
                    'session' => $session,
                ]);
            }

            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('success', 'Sesión de caja abierta correctamente.');
        } catch (BusinessLogicException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['session' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified session.
     */
    public function show(CashRegisterSession $session)
    {
        $this->authorize('view', $session);

        $session->load([
            'user',
            'openedByUser',
            'closedByUser',
        ]);

        $movements = $session->movements()
            ->with(['user:id,first_name,last_name', 'paymentMethod:id,name'])
            ->latest('movement_at')
            ->paginate(10);

        return view('admin.cash-register.show', compact('session', 'movements'));
    }

    /**
     * Show the close session form.
     */
    public function closeForm(CashRegisterSession $session)
    {
        $this->authorize('close', $session);

        if (! $session->is_open) {
            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('error', 'Esta sesión no está abierta.');
        }

        $session->load([
            'movements' => function ($query) {
                $query->latest('movement_at');
            },
        ]);

        return view('admin.cash-register.close', compact('session'));
    }

    /**
     * Close a cash register session.
     */
    public function close(CloseCashSessionRequest $request, CashRegisterSession $session)
    {
        try {
            $data = CashSessionCloseData::fromRequest(
                $request->validated() + ['closed_by_user_id' => $request->user()->id],
                $session->id
            );

            $session = $this->cashRegisterService->closeSession($data);

            $message = 'Sesión de caja cerrada correctamente.';

            if ($session->has_difference) {
                $message .= " Diferencia detectada: {$session->formatted_difference} ({$session->difference_type})";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'session' => $session,
                ]);
            }

            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('success', $message);
        } catch (BusinessLogicException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['session' => $e->getMessage()]);
        }
    }

    /**
     * Suspend a session.
     */
    public function suspend(CashRegisterSession $session)
    {
        $this->authorize('suspend', $session);

        try {
            $this->cashRegisterService->suspendSession($session, auth()->id());

            return redirect()
                ->route('admin.cash-register.index')
                ->with('success', 'Sesión suspendida correctamente.');
        } catch (BusinessLogicException $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }
    }

    /**
     * Resume a suspended session.
     */
    public function resume(CashRegisterSession $session)
    {
        $this->authorize('resume', $session);

        try {
            $this->cashRegisterService->resumeSession($session, auth()->id());

            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('success', 'Sesión reanudada correctamente.');
        } catch (BusinessLogicException $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }
    }

    /**
     * Show the form to register a manual movement.
     */
    public function movementForm(CashRegisterSession $session)
    {
        $this->authorize('update', $session);

        if (! $session->is_open) {
            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('error', 'Esta sesión no está abierta.');
        }

        $session->load(['user', 'openedByUser']);
        $paymentMethods = PaymentMethod::active()->get();

        return view('admin.cash-register.movement', compact('session', 'paymentMethods'));
    }

    /**
     * Record a manual movement (deposit/withdrawal).
     */
    public function recordMovement(CashMovementRequest $request, CashRegisterSession $session)
    {
        try {
            $data = CashMovementData::fromRequest(
                $request->validated() + ['user_id' => $request->user()->id],
                $session->id
            );

            $movement = $this->cashRegisterService->recordMovement($data);

            return redirect()
                ->route('admin.cash-register.show', $session)
                ->with('success', "Movimiento registrado: {$movement->type->label()} por {$movement->formatted_amount}");
        } catch (BusinessLogicException $e) {
            return back()->withErrors(['movement' => $e->getMessage()]);
        }
    }

    /**
     * Get my active session (API endpoint for AJAX).
     */
    public function mySession(Request $request)
    {
        $session = $this->cashRegisterService->getActiveSessionForUser($request->user()->id);

        if (! $session) {
            return response()->json(['session' => null, 'has_session' => false]);
        }

        return response()->json([
            'session' => [
                'id' => $session->id,
                'opening_balance' => $session->formatted_opening_balance,
                'expected_closing_balance' => $session->formatted_expected_closing_balance,
                'expected_closing_balance_raw' => $session->expected_closing_balance->getAmount()->toFloat(),
                'total_income' => $session->formatted_total_income,
                'total_expense' => $session->formatted_total_expense,
                'movements_count' => $session->movements()->count(),
                'opened_at' => $session->formatted_opened_at,
            ],
            'has_session' => true,
        ]);
    }

    /**
     * Get the last closed balance for the authenticated user.
     */
    public function lastClosingBalance(Request $request)
    {
        $lastSession = CashRegisterSession::query()
            ->where('user_id', $request->user()->id)
            ->where('status', CashRegisterSessionStatus::Closed)
            ->latest('closed_at')
            ->first();

        if (! $lastSession || ! $lastSession->actual_closing_balance) {
            return response()->json([
                'amount' => '0.00',
                'formatted' => 'C$ 0.00',
            ]);
        }

        return response()->json([
            'amount' => $lastSession->actual_closing_balance->getAmount()->toScale(2),
            'formatted' => $lastSession->formatted_actual_closing_balance,
        ]);
    }
}
