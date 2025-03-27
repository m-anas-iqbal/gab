<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;

class RoleController extends BaseController
{
    // List all roles
    public function index(Request $request)
    {
        $roles = Role::where('organization_id',$request->organization_id)->orWhere('organization_id',null)->get();
        return $this->sendResponse($roles, 'Roles retrieved successfully.');
    }

    // Create a new role
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|unique:roles,name,NULL,id,organization_id,' . $request->organization_id,
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }
        $role = Role::create(['name' => $request->name,'organization_id' => $request->organization_id,'guard_name'=>'web']);

        return $this->sendResponse($role, 'Role created successfully.');
    }

    // Show a specific role
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->sendError('Role not found.');
        }

        return $this->sendResponse($role, 'Role retrieved successfully.');
    }

    // Update a role
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->sendError('Role not found.');
        }

        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|unique:roles,name,' . $id . ',id,organization_id,' . $request->organization_id,
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }
        $role->update(['name' => $request->name,'guard_name'=>'web']);

        return $this->sendResponse($role, 'Role updated successfully.');
    }

    // Delete a role
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->sendError('Role not found.');
        }

        $role->delete();

        return $this->sendResponse(null, 'Role deleted successfully.');
    }
}
