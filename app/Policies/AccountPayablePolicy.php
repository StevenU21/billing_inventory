<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class AccountPayablePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read account_payables');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read account_payables');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create account_payables');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update account_payables');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export account_payables');
    }
}
