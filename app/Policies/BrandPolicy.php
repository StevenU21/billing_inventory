<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class BrandPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read brands');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read brands');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create brands');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update brands');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy brands');
    }
}

