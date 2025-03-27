<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class PermissionController extends BaseController
{
    // List all permissions
    public function index()
    {
        $permissions = Permission::all();
        return $this->sendResponse($permissions, 'Permissions retrieved successfully.');
    }

    // Create a new permission
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name,'guard_name'=>'web']);

        return $this->sendResponse($permission, 'Permission created successfully.');
    }

    // Show a specific permission
    public function show($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError('Permission not found.');
        }

        return $this->sendResponse($permission, 'Permission retrieved successfully.');
    }

    // Update a permission
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError('Permission not found.');
        }

        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        $permission->update(['name' => $request->name,'guard_name'=>'web']);

        return $this->sendResponse($permission, 'Permission updated successfully.');
    }

    // Delete a permission
    public function destroy($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError('Permission not found.');
        }

        $permission->delete();

        return $this->sendResponse(null, 'Permission deleted successfully.');
    }
}
