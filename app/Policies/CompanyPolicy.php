<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class CompanyPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read companies');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read companies');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create companies');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update companies');
    }
}

