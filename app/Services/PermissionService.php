<?php

namespace App\Services;

use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Facades\Permissions;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getPermissionGroupsForUser(User $user, ?array $selectedIds = null): array
    {
        $directPermissionModels = $user->getDirectPermissions();
        $rolePermissionModels = $user->getPermissionsViaRoles();

        $directPermissionIds = $directPermissionModels->pluck('id')->toArray();
        $rolePermissionNames = $rolePermissionModels->pluck('name');

        $specialPermissionModels = Permission::query()
            ->whereNotIn('name', $rolePermissionNames)
            ->get();

        $selectedIds = $selectedIds ?? $directPermissionIds;
        if (!is_array($selectedIds)) {
            $selectedIds = $directPermissionIds;
        }

        $rolePermissionGroups = Permissions::buildPermissionGroups($rolePermissionModels);
        $rolePermissionGroups = $this->ensurePermissionGroupsHaveSearchLabel($rolePermissionGroups);

        $permissionGroups = Permissions::buildPermissionGroups($specialPermissionModels, null, $selectedIds);
        $permissionGroups = $this->ensurePermissionGroupsHaveSearchLabel($permissionGroups);

        return [
            'rolePermissionGroups' => $rolePermissionGroups,
            'permissionGroups' => $permissionGroups,
        ];
    }

    public function assignPermissions(User $user, array $permissionIds): void
    {
        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->pluck('name')
            ->all();

        $user->syncPermissions($permissions);
    }

    public function revokePermissions(User $user, array $permissionNames): array
    {
        $revokedPermissions = [];
        $inheritedPermissions = [];

        foreach ($permissionNames as $permission) {
            if ($user->hasDirectPermission($permission)) {
                $user->revokePermissionTo($permission);
                $revokedPermissions[] = $permission;
            } else {
                $inheritedPermissions[] = $permission;
            }
        }

        return [
            'revoked' => $revokedPermissions,
            'inherited' => $inheritedPermissions,
        ];
    }

    private function ensurePermissionGroupsHaveSearchLabel(array $permissionGroups): array
    {
        return array_map(static function (array $group) {
            $group['permissions'] = array_map(static function (array $permission) {
                if (!array_key_exists('labelSearch', $permission)) {
                    $permission['labelSearch'] = mb_strtolower((string) ($permission['label'] ?? ''), 'UTF-8');
                }
                return $permission;
            }, $group['permissions'] ?? []);

            return $group;
        }, $permissionGroups);
    }
}
