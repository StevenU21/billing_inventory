<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class BackupPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read backups');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read backups');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'read backups');
    }

    public function restore(User $user): bool
    {
        return $this->checkPermission($user, 'read backups');
    }

    public function download(User $user): bool
    {
        return $this->checkPermission($user, 'download backups');
    }

    public function delete(User $user): bool
    {
        return $this->checkPermission($user, 'delete backups');
    }
}

