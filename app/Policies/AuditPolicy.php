<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class AuditPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read audits');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read audits');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export audits');
    }
}
