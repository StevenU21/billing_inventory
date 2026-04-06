<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class PurchasePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read purchases');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read purchases');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create purchases');
    }

    public function update(User $user, \App\Models\Purchase $purchase): bool
    {
        // Only Draft purchases can be edited
        if ($purchase->status !== \App\Enums\PurchaseStatus::Draft) {
            return false;
        }

        return $this->checkPermission($user, 'update purchases');
    }

    public function destroy(User $user, \App\Models\Purchase $purchase): bool
    {
        // Only Draft purchases can be deleted
        if ($purchase->status !== \App\Enums\PurchaseStatus::Draft) {
            return false;
        }

        return $this->checkPermission($user, 'destroy purchases');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export purchases');
    }
}

