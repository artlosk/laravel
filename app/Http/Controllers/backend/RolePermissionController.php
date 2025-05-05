<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Requests\UpdateUserRolesPermissionsRequest;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Controller for managing roles and permissions in the admin panel.
 */
class RolePermissionController extends Controller
{
    private const PAGINATION_LIMIT = 10;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function indexRoles(): View
    {
        $this->authorize('manage-roles');
        $roles = Role::paginate(self::PAGINATION_LIMIT);
        return view('backend.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function createRole(): View
    {
        $this->authorize('manage-roles');
        $permissions = Permission::all();
        return view('backend.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRole(StoreRoleRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $role = Role::create(['name' => $request->input('name')]);
                if ($request->input('permissions')) {
                    $role->syncPermissions($request->input('permissions'));
                }
            });
            return redirect()->route('backend.roles.index')->with('success', __('messages.role_created'));
        } catch (\Exception $e) {
            Log::error('Failed to create role: ' . $e->getMessage());
            return redirect()->route('backend.roles.create')->with('error', __('messages.role_creation_failed'));
        }
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param Role $role
     * @return \Illuminate\View\View
     */
    public function editRole(Role $role): View
    {
        $this->authorize('manage-roles');
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('backend.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $role) {
                $role->update(['name' => $request->input('name')]);
                $role->syncPermissions($request->input('permissions', []));
            });
            return redirect()->route('backend.roles.index')->with('success', __('messages.role_updated'));
        } catch (\Exception $e) {
            Log::error('Failed to update role: ' . $e->getMessage());
            return redirect()->route('backend.roles.edit', $role)->with('error', __('messages.role_update_failed'));
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyRole(Role $role): RedirectResponse
    {
        $this->authorize('manage-roles');
        if ($role->users()->count() > 0) {
            return redirect()->route('backend.roles.index')->with('error', __('messages.role_in_use'));
        }
        try {
            $role->delete();
            return redirect()->route('backend.roles.index')->with('success', __('messages.role_deleted'));
        } catch (\Exception $e) {
            Log::error('Failed to delete role: ' . $e->getMessage());
            return redirect()->route('backend.roles.index')->with('error', __('messages.role_deletion_failed'));
        }
    }

    /**
     * Display a listing of permissions.
     *
     * @return \Illuminate\View\View
     */
    public function indexPermissions(): View
    {
        $this->authorize('manage-permissions');
        $permissions = Permission::paginate(self::PAGINATION_LIMIT);
        return view('backend.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function createPermission(): View
    {
        $this->authorize('manage-permissions');
        return view('backend.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param StorePermissionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePermission(StorePermissionRequest $request): RedirectResponse
    {
        try {
            Permission::create(['name' => $request->input('name')]);
            return redirect()->route('backend.permissions.index')->with('success', __('messages.permission_created'));
        } catch (\Exception $e) {
            Log::error('Failed to create permission: ' . $e->getMessage());
            return redirect()->route('backend.permissions.create')->with('error', __('messages.permission_creation_failed'));
        }
    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param Permission $permission
     * @return \Illuminate\View\View
     */
    public function editPermission(Permission $permission): View
    {
        $this->authorize('manage-permissions');
        return view('backend.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission in storage.
     *
     * @param UpdatePermissionRequest $request
     * @param Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermission(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        try {
            $permission->update(['name' => $request->input('name')]);
            return redirect()->route('backend.permissions.index')->with('success', __('messages.permission_updated'));
        } catch (\Exception $e) {
            Log::error('Failed to update permission: ' . $e->getMessage());
            return redirect()->route('backend.permissions.edit', $permission)->with('error', __('messages.permission_update_failed'));
        }
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $this->authorize('manage-permissions');
        if ($permission->roles()->count() > 0 || $permission->users()->count() > 0) {
            return redirect()->route('backend.permissions.index')->with('error', __('messages.permission_in_use'));
        }
        try {
            $permission->delete();
            return redirect()->route('backend.permissions.index')->with('success', __('messages.permission_deleted'));
        } catch (\Exception $e) {
            Log::error('Failed to delete permission: ' . $e->getMessage());
            return redirect()->route('backend.permissions.index')->with('error', __('messages.permission_deletion_failed'));
        }
    }

    /**
     * Show the form for managing user roles and permissions.
     *
     * @return \Illuminate\View\View
     */
    public function manageUserRolesPermissions(): View
    {
        $this->authorize('manage-roles');
        $this->authorize('manage-permissions');
        $users = User::paginate(self::PAGINATION_LIMIT);
        $roles = Role::all();
        $permissions = Permission::all();
        return view('backend.roles.manage', compact('users', 'roles', 'permissions'));
    }

    /**
     * Update user roles and permissions.
     *
     * @param UpdateUserRolesPermissionsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserRolesPermissions(UpdateUserRolesPermissionsRequest $request): RedirectResponse
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            DB::transaction(function () use ($request, $user) {
                $user->syncRoles($request->input('roles', []));
                $user->syncPermissions($request->input('permissions', []));
            });
            return redirect()->route('backend.roles.manage')->with('success', __('messages.user_roles_permissions_updated'));
        } catch (\Exception $e) {
            Log::error('Failed to update user roles/permissions: ' . $e->getMessage());
            return redirect()->route('backend.roles.manage')->with('error', __('messages.user_roles_permissions_update_failed'));
        }
    }

    /**
     * Get user roles and permissions for AJAX requests.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRolesPermissions(User $user): JsonResponse
    {
        $this->authorize('manage-roles');
        $this->authorize('manage-permissions');
        return response()->json([
            'roles' => $user->roles->pluck('name')->toArray(),
            'permissions' => $user->permissions->pluck('name')->toArray(),
        ]);
    }
}
