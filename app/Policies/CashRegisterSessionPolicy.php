<?php

namespace App\Policies;

use App\Models\CashRegisterSession;
use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class CashRegisterSessionPolicy
{
    use HasPermissionCheck;

    /**
     * Determine if the user can view any sessions.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read cash_register');
    }

    /**
     * Determine if the user can view a specific session.
     */
    public function view(User $user, CashRegisterSession $session): bool
    {
        return $this->checkPermission($user, 'read cash_register');
    }

    /**
     * Determine if the user can create a session (open).
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create cash_register');
    }

    /**
     * Determine if the user can open a new session.
     */
    public function open(User $user): bool
    {
        return $this->checkPermission($user, 'open cash_register');
    }

    /**
     * Determine if the user can close a session.
     */
    public function close(User $user, CashRegisterSession $session): bool
    {
        // Only the user who opened the session or admin can close it
        if ($session->user_id !== $user->id && ! $user->hasRole('admin')) {
            return false;
        }

        return $this->checkPermission($user, 'close cash_register');
    }

    /**
     * Determine if the user can suspend a session.
     */
    public function suspend(User $user, CashRegisterSession $session): bool
    {
        if ($session->user_id !== $user->id && ! $user->hasRole('admin')) {
            return false;
        }

        return $this->checkPermission($user, 'suspend cash_register');
    }

    /**
     * Determine if the user can resume a session.
     */
    public function resume(User $user, CashRegisterSession $session): bool
    {
        if ($session->user_id !== $user->id && ! $user->hasRole('admin')) {
            return false;
        }

        return $this->checkPermission($user, 'resume cash_register');
    }

    /**
     * Determine if the user can make deposits.
     */
    public function deposit(User $user): bool
    {
        return $this->checkPermission($user, 'deposit cash_register');
    }

    /**
     * Determine if the user can make withdrawals.
     */
    public function withdraw(User $user): bool
    {
        return $this->checkPermission($user, 'withdraw cash_register');
    }

    /**
     * Determine if the user can record movements in a session.
     */
    public function recordMovement(User $user, CashRegisterSession $session): bool
    {
        // User must be the session owner or admin
        if ($session->user_id !== $user->id && ! $user->hasRole('admin')) {
            return false;
        }

        // Session must be open
        if (! $session->is_open) {
            return false;
        }

        return $this->checkPermission($user, 'create cash_register');
    }
}
