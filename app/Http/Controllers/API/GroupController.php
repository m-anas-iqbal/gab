<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroupController extends BaseController
{
    /**
     * Fetch all groups
     */
    public function index(Request $request)
    {
         // Retrieve all groups for the organization
    $groups = Group::where('organization_id', $request->organization_id)->get();

    // // Iterate through each group and append the member count
    $groupsWithMemberCount = $groups->map(function($group) {
        $group->member_count = $group->memberCount(); // Add member count to each group
        return $group;
    });

    return $this->sendResponse($groupsWithMemberCount, 'Groups retrieved successfully.');
    }

    /**
     * Fetch a specific group
     */
    public function show($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }

        return $this->sendResponse($group, 'Group retrieved successfully.');
    }

    /**
     * Create a new group
     */
    public function store(Request $request, Helper $helper)
    {
        // Validate the request data
        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|integer',
            'created_by' => 'required|exists:users,id', // Assuming 'created_by' references a user
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        // Initialize an empty data array
        $data = $validatedData->validated();  // Get only the validated fields
        $data['code'] = $this->generateUniqueCode();
        // Handle file uploads for banner
        if ($request->hasFile('banner')) {
            $data['banner'] = $helper->uploader($request, 'banner', 'uploaded/organization/groups/banners');
        }

        // Handle file uploads for avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $helper->uploader($request, 'avatar', 'uploaded/organization/groups/avatars');
        }

        // Create a new group using the validated data and file paths
        $group = Group::create($data);

        return $this->sendResponse($group, 'Group created successfully.', 201); // HTTP 201 for Created
    }
    private function generateUniqueCode()
    {
        do {
            // Generate random code
            $code = strtoupper(Str::random(8));  // Generate a random 8-character string
        } while (Group::where('code', $code)->exists());  // Ensure the code is unique

        return $code;
    }
    /**
     * Update an existing group
     */
    public function update(Request $request, $id, Helper $helper)
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }

        // Validate the request data
        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|integer',
            'created_by' => 'sometimes|required|exists:users,id', // Sometimes required for updates
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        // Initialize an empty data array
        $data = $validatedData->validated();  // Get only the validated fields

        // Handle file uploads for banner
        if ($request->hasFile('banner')) {
            $data['banner'] = $helper->uploader($request, 'banner', 'uploaded/organization/groups/banners');
        }

        // Handle file uploads for avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $helper->uploader($request, 'avatar', 'uploaded/organization/groups/avatars');
        }

        // Update the group with validated data
        $group->update($data);

        return $this->sendResponse($group, 'Group updated successfully.'); // HTTP 200 for OK
    }
    /**
     * Reset a group code
     */
    public function resetCode(Request $request, $id)
    {
        $group = Group::find($id);
        if (!$group) {
            return $this->sendError('Group not found', [], 422);
        }
        $newCode = $this->generateUniqueCode();
        // dd($request->all(),$id);/
        $group->code = $newCode;
        $group->save();
        return $this->sendResponse($group, 'Group code reset successfully.', 200); // HTTP 200 for OK
    }
    /**
     * Delete a group
     */
    public function destroy($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }

        $group->delete();

        return $this->sendResponse([], 'Group deleted successfully.'); // HTTP 200 for OK
    }
}
