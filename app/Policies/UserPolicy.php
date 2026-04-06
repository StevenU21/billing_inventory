<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class UserPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read users');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read users');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create users');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update users');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy users');
    }

    public function reactivate(User $user): bool
    {
        return $this->checkPermission($user, 'reactivate users');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export users');
    }
}

