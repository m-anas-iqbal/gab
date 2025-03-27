<?php

namespace App\Http\Controllers\API;

use App\Models\GroupMember;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\GroupInviteMail;
use App\Jobs\SendGroupInviteEmail;
use App\Jobs\SendGroupCodeEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberGroupStatus;
use App\Helpers\Helper;

class GroupMemberController extends BaseController
{
    /**
     * Fetch all members of a group.
     */
    public function index($groupId)
    {
        $members = GroupMember::with('user')->where('group_id',$groupId)->get();
        return $this->sendResponse($members, 'Active group members retrieved successfully.');
    }
    /**
     * Fetch all members of a group.
     */
    public function GetAllGroupMembers(Request $request)
    {
        $organization = GroupMember::with('user','group:id,name')->where('organization_id',$request->organization_id)->get();
        return $this->sendResponse($organization, 'Organization Group members retrieved successfully.');
    }

    /**
     * Handle a request to join a group (Pending status initially).
     */
    public function requestToJoin(Request $request)
    {
        // Validate the incoming request
        $validatedData = Validator::make($request->all(), [
            'group_code' => 'required|exists:groups,code', // Validate using group code
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }

        // Get validated data
        $data = $validatedData->validated();
        $data['status'] = 0; // Pending status

        // Find the group by the group code
        $group = Group::where('code', $data['group_code'])->first();

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }

        // Check if the user is already a member or has a pending request
        $existingMember = GroupMember::where('group_id', $group->id)
                                     ->where('user_id', $data['user_id'])
                                     ->first();

        if ($existingMember) {
            return $this->sendError('User has already requested to join this group or is already a member.', [], 409);
        }

        // Create the group member entry
        $groupMember = GroupMember::create([
            'organization_id' =>  $group->organization_id,
            'group_id' => $group->id,
            'user_id' => $data['user_id'],
            'status' => $data['status'],
        ]);
        if ($groupMember) {
            $user =User::with('organization')->where('id', $request->user_id)->first();
            $user->group =  $group;
            $userIds[] =$user->organization->admin_id;
            $title = $user->name." wants to join a ".$group->name. ' group';
            Helper::sendFcmNotification($userIds,  $title,  "", "",$user);
        }
        return $this->sendResponse($groupMember, 'Join request sent successfully.');
    }
    /**
     * Approve a join request.
     */
    public function approveJoinRequest($groupId, $userId)
    {
        $groupMember = GroupMember::where('group_id', $groupId)->where('user_id', $userId)->first();

        if (!$groupMember) {
            return $this->sendError('Join request not found', [], 404);
        }

        if ($groupMember->status != 0) {
            return $this->sendError('This request has already been processed.', [], 400);
        }

        $groupMember->update(['status' => 1]); // Approve and make the user active

        Mail::to($groupMember->user->email)->send(new MemberGroupStatus('approved',$groupMember->group->name,$groupMember->organization->name));
        return $this->sendResponse($groupMember, 'Join request approved and user is now active.');
    }
    /**
     * Update the status of multiple join requests by providing GroupMember IDs and their new status.
     */
    public function updateJoinRequests(Request $request)
    {
        // Get the array of group_member IDs and statuses from the request
        $groupMembersData = $request->input('group_members'); // An array of objects with id and status

        // Validate that group_members is provided
        if (!is_array($groupMembersData) || empty($groupMembersData)) {
            return $this->sendError('group_members must be provided, and it should be an array.', [], 400);
        }

        // Validate that each group member has an id and status
        foreach ($groupMembersData as $member) {
            if (empty($member['id']) || !in_array($member['status'], [1, 2])) {
                return $this->sendError('Each group member must have an id and a valid status (1 for approve, 2 for deny).', [], 400);
            }
        }

        // Loop through each group member to update their status
        foreach ($groupMembersData as $member) {
            $status = 'rejected';
            // Find the group member by their ID
            $groupMember = GroupMember::find($member['id']);

            // Check if the group member exists
            if (!$groupMember) {
                return $this->sendError("GroupMember with ID {$member['id']} not found.", [], 404);
            }

            // Update the status of the group member
            $groupMember->status = $member['status'];
            $groupMember->save();
            if ($member['status'] == 1) {
                $status = 'approved';
            }
            Mail::to($groupMember->user->email)->send(new MemberGroupStatus($status,$groupMember->group->name,$groupMember->organization->name));

        }

        // Return success message
        return $this->sendResponse([], 'Join requests have been updated successfully.');
    }
    /**
     * Deny a join request.
     */
    public function denyJoinRequest($groupId, $userId)
    {
        $groupMember = GroupMember::where('group_id', $groupId)->where('user_id', $userId)->first();

        if (!$groupMember) {
            return $this->sendError('Join request not found', [], 404);
        }

        if ($groupMember->status != 0) {
            return $this->sendError('This request has already been processed.', [], 400);
        }

        $groupMember->update(['status' => 2]);  // Deny the request by deleting the record
        Mail::to($groupMember->user->email)->send(new MemberGroupStatus('rejected',$groupMember->group->name,$groupMember->organization->name));

        return $this->sendResponse([], 'Join request denied successfully.');
    }

    /**
     * Add multiple members to a group.
     */
    public function addMembers(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'members' => 'required|array',
            // 'members.id*' => 'exists:users,id',  // Each member in the array must exist in the users table
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }

        $data = $validatedData->validated();
        $groupId = $data['group_id'];
        $members = $data['members'];

        $group = Group::find($groupId);
        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }
        foreach ($members as $user) {
            $existingMember = GroupMember::where('group_id', $groupId)
                                         ->where('user_id', $user['id'])
                                         ->first();

            if (!$existingMember) {
                GroupMember::create([
                    'organization_id' =>  $group->organization_id,
                    'group_id' => $groupId,
                    'user_id' => $user['id'],
                    'is_admin' => $user['is_admin'],
                    'status' => 1,  // Active status
                ]);
                if ($user['is_admin'] == 1) {
                    $member = User::find($id);
                    $member->assignRole('group_admin');
                }
            }else {
                return $this->sendError('User is already a member of this group.', [], 422);
            }
        }

        return $this->sendResponse($data, 'Members added to group successfully.');
    }

    /**
     * Generate an invite link for a group.
     */
    public function generateInviteLink(Request $request, $groupId)
    {
        // dd($request->all(),$groupId);
        // Validate the incoming request
        $validatedData = Validator::make($request->all(), [
            'emails' => 'required|array',
            'emails.*' => 'required|email', // Each item in the emails array should be a valid email address
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }
        // Find the group by its ID
        $group = Group::find($groupId);

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }
        $emails = $validatedData->validated()['emails'];
        foreach ($emails as $email) {
            $data = [
                'group_code' => $group->code,
                'email' => $email,
            ];
            $jsonData = json_encode($data);
            $encodedData = base64_encode($jsonData);
            $inviteLink = config('app.front_url') . "register/individual?query=" . $encodedData;
            SendGroupInviteEmail::dispatch($email, $inviteLink, $group->name);

        }

        // Respond with the invite link
        return $this->sendResponse(['invite_link' => $inviteLink], 'Invite link generated and emails queued successfully.');
    }

    /**
     * Generate an invite link for a group.
     */
    public function generateInviteCode(Request $request, $groupId)
    {
        // dd($request->all(),$groupId);
        // Validate the incoming request
        $validatedData = Validator::make($request->all(), [
            'emails' => 'required|array',
            'emails.*' => 'required|email', // Each item in the emails array should be a valid email address
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error.', $validatedData->errors(), 422);
        }
        // Find the group by its ID
        $group = Group::find($groupId);

        if (!$group) {
            return $this->sendError('Group not found', [], 404);
        }
        // Get the validated emails from the request
        $emails = $validatedData->validated()['emails'];

        // Dispatch a job to send invite emails for each recipient
        foreach ($emails as $email) {
            // Queue the email sending
            // Mail::to($email)->send(new GroupInviteMail($inviteLink, $group->name));
            SendGroupCodeEmail::dispatch($email, $group->code, $group->name);
        }
        // Respond with the invite link
        return $this->sendResponse(['invite_link' => $group->code,'group_name'=> $group->name], 'Invite link generated and emails queued successfully.');
    }
    /**
     * Make a member an admin of the group.
     */
    public function makeAdmin(Request $request)
    {
        $groupMember = GroupMember::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();

        if (!$groupMember) {
            return $this->sendError('Member not found in the group.', [], 404);
        }

        if ($groupMember->is_admin) {
            return $this->sendError('User is already an admin.', [], 409);
        }

        $groupMember->update(['is_admin' => 1]); // Make the user an admin

        return $this->sendResponse($groupMember, 'Member promoted to admin successfully.');
    }

    /**
     * Remove admin rights from a member.
     */
    public function removeAdmin(Request $request)
    {
        $groupMember = GroupMember::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();

        if (!$groupMember) {
            return $this->sendError('Member not found in the group.', [], 404);
        }

        if (!$groupMember->is_admin) {
            return $this->sendError('User is not an admin.', [], 409);
        }

        $groupMember->update(['is_admin' => 0]); // Remove admin rights

        return $this->sendResponse($groupMember, 'Admin rights revoked successfully.');
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Request $request)
    {
        $groupMember = GroupMember::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();
        if (!$groupMember) {
            return $this->sendError('Member not found in the group.', [], 404);
        }

        $groupMember->forceDelete(); // Remove the member from the group

        return $this->sendResponse([], 'Member removed from group successfully.');
    }
    public function leaveGroup(Request $request)
    {
        // Find the group member record
        $groupMember = GroupMember::where('group_id', $request->group_id)->where('user_id', $request->user_id)->first();

        // If the member is not found, return a 404 error
        if (!$groupMember) {
            return $this->sendError('You are not a member of this group.', [], 404);
        }

        // Perform hard delete (permanently remove the record, even if soft deletes are enabled)
        $groupMember->forceDelete();

        // Return a success response
        return $this->sendResponse([], 'You have successfully left the group.');
    }
}
