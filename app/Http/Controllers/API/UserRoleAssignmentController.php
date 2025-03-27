<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class UserRoleAssignmentController extends BaseController
{
    // Assign role to a user
    public function assignRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->sendError('User not found.');
        }

        $data[] = $role = Role::find($request->role_id);

        if (!$role) {
            return $this->sendError('Role not found.');
        }

        $data[] = $user->syncRoles($role->name);

        // Get the roles associated with the user
        $data[] = $user->roles->pluck('name');

        return $this->sendResponse($data , 'Role assigned successfully.');
    }

    // Update role permissions
    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array', // Accept an array of permission IDs
            'permissions.*' => 'integer',
            'role_id' => 'required|integer',
        ]);

        $role = Role::find($request->role_id);
        $data['role'] = $role;
        if (!$role) {
            return $this->sendError('Role not found.');
        }
        // Validate that all permissions exist
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        if ($permissions->isEmpty()) {
            return $this->sendError('No valid permissions found.');
        }

            $data['permissions'] = $permissions;
        // dd($request->permissions,$permissions);
        // Sync the role's permissions (replaces existing permissions with new ones)
        $role->syncPermissions($permissions);

        return $this->sendResponse($data, 'Role permissions updated successfully.');
    }

    // Helper to fetch a role with its permissions
    public function getRoleWithPermissions($roleId)
    {
        $role = Role::where('id', $roleId)->with('permissions')->first();
        if (!$role) {
            return $this->sendError('Role not found.');
        }
        return $this->sendResponse($role->permissions->pluck("id"), 'Role with permissions retrieved successfully.');
    }
}
