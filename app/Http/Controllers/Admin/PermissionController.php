<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\RevokePermissionsRequest;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use AuthorizesRequests;

    public function edit(User $user, PermissionService $permissionService)
    {
        $this->authorize('view', Permission::class);

        $permissionData = $permissionService->getPermissionGroupsForUser($user, old('permission_ids'));

        return view('admin.permissions.edit', [
            'user' => $user,
            'rolePermissionGroups' => $permissionData['rolePermissionGroups'],
            'permissionGroups' => $permissionData['permissionGroups'],
        ]);
    }

    public function assignPermission(AssignPermissionsRequest $request, User $user, PermissionService $permissionService)
    {
        $permissionIds = $request->validated()['permission_ids'] ?? [];
        $permissionService->assignPermissions($user, $permissionIds);
        return back()->with('success', 'Permisos actualizados correctamente.');
    }

    public function revokePermission(RevokePermissionsRequest $request, User $user, PermissionService $permissionService)
    {
        $permissionNames = $request->validated()['permission'];
        $result = $permissionService->revokePermissions($user, $permissionNames);

        $message = 'Permisos revocados correctamente';
        if (!empty($result['inherited'])) {
            $message .= '. Los siguientes permisos son heredados de roles y no pueden ser revocados: ' . implode(', ', $result['inherited']);
        }

        return back()->with('updated', $message);
    }
}
