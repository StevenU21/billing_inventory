<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class InventoryPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read inventories');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read inventories');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create inventories');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update inventories');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy inventories');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export inventories');
    }
}
