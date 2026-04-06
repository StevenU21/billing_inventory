<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CashRegisterSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\CashRegisterSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user && $user->hasRole('cashier')) {
            return redirect()->intended(route('admin.sales.index'));
        }

        return redirect()->intended(route('admin.sales.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = $request->user()?->id;

        if ($userId) {
            $hasOpenSession = CashRegisterSession::where('user_id', $userId)
                ->where('status', CashRegisterSessionStatus::Open)
                ->exists();

            if ($hasOpenSession) {
                return back()->withErrors([
                    'session' => 'No puede cerrar sesión sin cerrar la caja.',
                ]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
