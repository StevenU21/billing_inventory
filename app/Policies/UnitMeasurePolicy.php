<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class UnitMeasurePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read unit_measures');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read unit_measures');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create unit_measures');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update unit_measures');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy unit_measures');
    }
}

