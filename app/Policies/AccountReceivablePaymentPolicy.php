<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class AccountReceivablePaymentPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read payments');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read payments');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create payments');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update payments');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export payments');
    }
}
