<?php

namespace App\Policies;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class ProductVariantPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read product_variants');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read product_variants');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create product_variants');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update product_variants');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy product_variants');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export product_variants');
    }
}

