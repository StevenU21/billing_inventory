<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class AccountReceivablePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read account_receivables');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read account_receivables');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create account_receivables');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update account_receivables');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export account_receivables');
    }
}
