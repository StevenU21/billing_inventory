<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class CategoryPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read categories');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read categories');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create categories');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update categories');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy categories');
    }
}

