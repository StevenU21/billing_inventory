<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class MunicipalityPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read municipalities');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read municipalities');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create municipalities');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update municipalities');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy municipalities');
    }
}

