<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class DepartmentPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read departments');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read departments');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create departments');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update departments');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy departments');
    }
}

