<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Order;
use App\Models\Post;
use App\Models\GroupMember;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Hash;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Spatie\Permission\Models\Role;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

class OrganizationController extends BaseController
{
     /**
     * Fetch all groups
     */
    public function dashboard(Request $request)
    {
        $data =[];
        $data['groups']  = Group::select('name','code','status','avatar','banner')->withCount('members')->where('organization_id', $request->organization_id)->get();
        $groups_id = $data['groups']->pluck('id');
        $data['active_members'] = User::where("organization_id", $request->organization_id)->where('status',1)->get();
        $data['pending_members'] = User::where("organization_id", $request->organization_id)->whereIn('status',[0,2])->get();
        $data['posts'] = Post::withCount(['likes', 'comments'])
        ->with('user')->whereIn('group_id',$groups_id)->get();
        return $this->sendResponse($data, 'Transaction retrieved successfully.');
    }

    public function transaction_history(Request $request)
    {
    $orders = Order::with('user','orderDetails.subscriptionPlan','organization')->where("organization_id",$request->organization_id)->get();
    return $this->sendResponse($orders, 'Transaction retrieved successfully.');
    }
    public function registration(Request $request)
    {
        // Validate the request input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'email_verified' => 'required|in:1,0',
            'password' => 'required|string|min:6|confirmed',
            'organization_name' => 'required|string|max:255',
            'organization_type' => 'required|integer',
            'address' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|email|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_admin' => 1,
                'image' => null,
                'status' => 1,
                'email_verified_at' => $request->email_verified ==0?null:now(), // Timestamp with Carbon
                'password' => Hash::make($request->password),
            ]);
            $role = Role::find(1); // Find the role with ID 2
            if ($role) {
                $user->assignRole($role->name); // Assign the role by its name
            }
            // 2. Create the organization with all the data
            $organization = Organization::create([
                'name' => $request->organization_name,
                'organization_type' => $request->organization_type,
                'address' => $request->address,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'state' => $request->state,
                'city' => $request->city,
                'country' => $request->country,
                'zipcode' => $request->zipcode,
                'website' => $request->website,
                'admin_id' => $user->id, // Assuming the user is the admin
            ]);

            // 3. Update the user with organization_id
            $user->organization_id = $organization->id;
            $user->save();

            // Commit the transaction
            DB::commit();
            $data['user'] = $user;
            $data['permissions']  = $role->permissions()->pluck('name')->toArray() ?? [];
            $data['token'] =  $user->createToken('MyApp')->accessToken;
            $data['organization'] = $organization;
            // $data['user'] =$organization->admin();
            $subject = 'Welcome to WorkAround! '.$user->name.', '.$organization->name;
            Mail::to($user->email)->send(new WelcomeMail($subject,$user->name));

            return $this->sendResponse($data, 'Admin and organization created successfully. Kindly select a plan for your organization to continue.');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Log the error (optional)
            \Log::error('User/Organization Creation Failed: ' . $e->getMessage());

            // Return an error response
            return $this->sendError('Registration failed, please try again later.', 500);
        }
    }
    public function updateOrganization(Request $request, $id,Helper $helper)
    {
        $organization = Organization::find($id);
        if (!$organization) {
            return $this->sendError('Organization not found.', [], 404);
        }
        $validator = Validator::make($request->all(), [
            'address'           => 'nullable|string',
            'contact_phone'     => 'nullable|string',
            'contact_email'     => 'nullable|email',
            'state'             => 'nullable|string',
            'city'              => 'nullable|string',
            'country'           => 'nullable|string',
            'name'              => 'nullable|string',
            'zipcode'           => 'nullable|string',
            'website'           => 'nullable|string',
            'banner'            => 'nullable|file|image|mimes:jpeg,png,jpg,gif',
            'logo'              => 'nullable|file|image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }
        $data = $validator->validated();
        if ($request->hasFile('banner')) {
            $data['banner'] = $helper->uploader($request, 'banner', 'uploaded/organization/banners');
        }
        if ($request->hasFile('logo')) {
            $data['logo'] = $helper->uploader($request, 'logo', 'uploaded/organization/logo');
        }
        $organization->update($data);
        return $this->sendResponse($organization, 'Organization updated successfully.');
    }

}
