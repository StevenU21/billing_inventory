<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Entity;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class EntityPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read clients') || $this->checkPermission($user, 'read suppliers');
    }

    public function view(User $user, Entity $entity): bool
    {
        if ($entity->is_client && $this->checkPermission($user, 'read clients')) {
            return true;
        }
        if ($entity->is_supplier && $this->checkPermission($user, 'read suppliers')) {
            return true;
        }
        return $this->checkPermission($user, 'read clients') || $this->checkPermission($user, 'read suppliers');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create clients') || $this->checkPermission($user, 'create suppliers');
    }

    public function update(User $user, Entity $entity): bool
    {
        if ($entity->is_client && $this->checkPermission($user, 'update clients')) {
            return true;
        }
        if ($entity->is_supplier && $this->checkPermission($user, 'update suppliers')) {
            return true;
        }
        return $this->checkPermission($user, 'update clients') || $this->checkPermission($user, 'update suppliers');
    }

    public function destroy(User $user): bool
    {
        return $this->checkPermission($user, 'destroy entities');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export entities');
    }
}

