<?php

namespace App\Policies;

use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;
use App\Models\User;

class UpdatePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read updates');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read updates');
    }

    public function check(User $user): bool
    {
        return $this->checkPermission($user, 'check updates');
    }

    public function download(User $user): bool
    {
        return $this->checkPermission($user, 'download updates');
    }

    public function install(User $user): bool
    {
        return $this->checkPermission($user, 'install updates');
    }
}

