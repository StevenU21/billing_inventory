<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class ProductPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read products');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read products');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create products');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update products');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy products');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export products');
    }
}

