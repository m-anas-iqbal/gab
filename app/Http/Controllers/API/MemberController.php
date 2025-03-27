<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController; // Correct namespace
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use App\Models\GroupMember;
use App\Models\Group;
use App\Models\Organization;

use App\Mail\MemberMail;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class MemberController extends BaseController
{
    /**
     * Fetch all members
     */
    public function index(Request $request)
    {
        $members = User::with('groups','group_admin')->where("organization_id", $request->organization_id)->get();
        return $this->sendResponse($members, 'Members retrieved successfully.');
    }

    /**
     * Fetch all members
     */
    public function deleted_index(Request $request)
    {
        $members = User::onlyTrashed()->with('groups','group_admin')->where("organization_id", $request->organization_id)->get();
        return $this->sendResponse($members, 'Members retrieved successfully.');
    }

    public function restore($id)
    {
        $member = User::onlyTrashed()->find($id);

        if (!$member) {
            return $this->sendError('Member not found', [], 404);
        }

        $member->restore();
        return $this->sendResponse($member, 'Member restored successfully.');
    }
    /**
     * Delete a member
     */
    public function hard_destroy($id)
    {
        $member = User::onlyTrashed()->find($id);

        if (!$member) {
            return $this->sendError('Member not found', [], 404);
        }

        $member->forceDelete();
        return $this->sendResponse($member, 'Member deleted successfully.');
    }
    /**
     * Fetch a specific member
     */
    public function show($id)
    {
        $member = User::with('groups','group_admin')->find($id);

        if (!$member) {
            return $this->sendError('Member not found', [], 404);
        }

        return $this->sendResponse($member, 'Member retrieved successfully.');
    }
/**
 * Fetch a specific member and their group
 */
public function getMemberGroup(Request $request)
{
    $member = GroupMember::with('group')->where('user_id', $request->user_id)->get() ?? [];
    return $this->sendResponse($member, 'Member Groups retrieved successfully.');
}
    /**
     * Create a new member
     */
    public function store(Request $request)
    {
        // Validate input
        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'status' => 'nullable|integer',
            'phone' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        // Initialize an empty data array
        $data = $validatedData->validated();  // Get only the validated fields
        $data['password'] = bcrypt($request->password); // Hash the password

        if ($request->hasFile('image')) {
            $data['image'] = Helper::uploader($request, 'image', 'uploaded/profile_image');
        } 
        $member = User::create($data);
        $member->assignRole('member');
        $organization =Organization::find($request->organization_id)->name;
        Mail::to($request->email)->send(new MemberMail($request->name,$request->email,$request->password,$organization));
        // Check if group_code is set and not empty
        if (isset($request->group_code) && $request->group_code != "") {
            // Find the group based on group_code
            $group = Group::where('code', $request->group_code)->first();
            $member->group =$group;
            // Check if the group exists
            if ($group) {
                // Create a new GroupMember entry
                GroupMember::create([
                    'organization_id' => $group->organization_id,
                    'group_id' => $group->id, // Use the found group's ID
                    'user_id' => $member->id,
                    'status' => 1,  // Active status
                ]);
                return response()->json(['message' => 'Group member added successfully.'], 200);
            } else {
                return response()->json(['error' => 'Group not found.'], 404);
            }
        }

        return $this->sendResponse($member, 'Member created successfully.', 201); // HTTP 201 for created
    }

    /**
     * Update an existing member
     */
    public function update(Request $request, $id)
    {
        $member = User::find($id);

        if (!$member) {
            return $this->sendError('Member not found', [], 404);
        }

        // Validate input
        $validatedData = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'sometimes|required|string|max:255',
            'status' => 'nullable|integer',
            'phone' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        $data = $validatedData->validated();  // Get only the validated fields
        if (!empty($request->password)) {
            $data['password'] = bcrypt($request->password); // Re-hash password if updated
        }

        if ($request->hasFile('image')) {
            $data['image'] = Helper::uploader($request, 'image', 'uploaded/profile_image');
        }

        $member->update($data);

        return $this->sendResponse($member, 'Member updated successfully.'); // HTTP 200 for OK
    }

    /**
     * Delete a member
     */
    public function destroy($id)
    {
        $member = User::find($id);

        if (!$member) {
            return $this->sendError('Member not found', [], 404);
        }

        $member->delete();

        return $this->sendResponse([], 'Member deleted successfully.'); // HTTP 200 for OK
    }
    /**
     * Update the status of a user.
     */
    public function updateUserStatus(Request $request)
    {
        // Validate the incoming request for `user_id` and `status`
        $validated = $request->validate([
            'id' => 'required|integer|exists:users,id',
            'status' => 'required',
        ]);
        // Find the User by ID
        $user = User::find($request->id);
        // Update the user's status
        $user->status = $request->status;
        $user->save();
        return $this->sendResponse([$user], 'Member status updated successfully.'); // HTTP 200 for OK
    }
}
