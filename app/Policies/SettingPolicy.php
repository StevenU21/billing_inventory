<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class SettingPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read settings');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read settings');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update settings');
    }
}

