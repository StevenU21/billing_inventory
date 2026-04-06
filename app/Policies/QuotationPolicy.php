<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class QuotationPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, "read quotations");
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, "read quotations");
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, "create quotations");
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, "update quotations");
    }

    public function delete(User $user): bool
    {
        return $this->checkPermission($user, "delete quotations");
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, "export quotations");
    }
}
