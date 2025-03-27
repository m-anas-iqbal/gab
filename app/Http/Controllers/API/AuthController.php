<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hash;
use App\Models\User;
use App\Models\GroupMember;
use App\Models\Group;
use App\Models\SubscriptionPlan;
use Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;
use Auth;
use App\Helpers\Helper;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
        /**
     * @OA\Post(
     * path="/api/register",
     *   tags={"Register"},
     *   summary="Register",
     *   operationId="register",
     *
     *  @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),

     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      @OA\Parameter(
     *      name="password_confirmation",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $data['last_device'] = $request->last_device ?? $request->ip();
            $data['status'] = 1;
            $data['last_login_date'] = now()->toDateTimeString();
            // Create the user
            $user = User::create($data);
            $user->assignRole('member');
            $success['token'] =  $user->createToken('authToken')->accessToken;
            $success['user'] =  $user;
            if (isset($request->group_code) && $request->group_code != "") {
                $group = Group::where('code', $request->group_code)->first();
                if ($group) {
                    // Create the group member
                    GroupMember::create([
                        'group_id' => $group->id,
                        'organization_id' => $group->organization_id,
                        'user_id' => $user->id,
                        'status' => 0,
                    ]);

                    // Assign the organization to the user
                    $user->organization_id = $group->organization_id;
                    $user->save();
                } else {
                    // Rollback if the group code is invalid
                    DB::rollBack();
                    return response()->json(['errors' => "Invalid group code."], 404);
                }
            }
            DB::commit();

            $subject = 'Welcome to WorkAround! '.$user->name;
            Mail::to($user->email)->send(new WelcomeMail($subject,$user->name));

            return response()->json(['success' => $success]);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                // Handle validation errors
                return response()->json(['errors' => $e->errors()], 422);
            }

            // Handle other unexpected errors
            return response()->json(['errors' => 'Something went wrong. Please try again later.'], 500);
        }
    }

       /**
     * @OA\Post(
     ** path="/api/login",
     *   tags={"Login"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/

     public function login(Request $request)
     {
         // Validate the request inputs
         $validator = Validator::make($request->all(), [
             'email' => 'required|email|exists:users,email', // Proper email validation
             'password' => 'required',
         ], ['email.exists' => "Email is not registered!"]);

         // If validation fails, return error response
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
         // Attempt to log the user in with credentials
         $credentials = $request->only('email', 'password');  // Extract email and password
         if (!auth()->attempt($credentials)) {
             return response()->json([
                 'success' => false,
                 'errors' => 'Unauthorized. Invalid email or password.',
             ], 401);  // Return Unauthorized status
         }

         // If it's a web login and web access is restricted
         if (isset($request->is_web) && $request->is_web == 1 && auth()->user()->organization_id == null) {
             return response()->json([
                 'errors' => ["As an individual account holder, web access is currently unavailable. Please use our mobile application for all services."]
             ], 422);
         }
          // If it's a web login and web access is restricted
          if (auth()->user()->status == 0) {
            return response()->json([
                'errors' => ["You don't have access for login, your organization head."]
            ], 422);
        }
        // Update last login time and IP address
        auth()->user()->last_login_date = now()->toDateTimeString();
        auth()->user()->last_device  = $request->device_name ?? $request->ip();
        auth()->user()->update();
         // Generate access token upon successful login
        $success['token'] = auth()->user()->createToken('authToken')->accessToken;
        $success['user'] = auth()->user() ?? [];
        if (auth()->user()->organization_id!= null)
        {
            $success['organization'] = auth()->user()->organization ?? [];
            $success['organization']['plan_name'] = SubscriptionPlan::where("plan_product_id",$success['organization']->plan_id)->first()->name ?? "";

            $success['user_id'] = auth()->user()->id ?? "";
            $user = auth()->user();
            $roleId = $user->roles->pluck('id')->first();
            if ($roleId !=null) {
                $role = Role::find($roleId)->first();
                $success['permissions']  = $role->permissions()->pluck('name')->toArray() ?? [];
            }else{
                $success['permissions']  = [];
            }
        }
        //  dd($roleId, $success,$role);
        // Return success response
        return response()->json(['success' => $success], 200);
     }


     public function social_login(Request $request)
     {
         // Validate the request inputs
         $validator = Validator::make($request->all(), [
             'email' => 'required|email|exists:users,email', // Proper email validation
            //  'password' => 'required',
         ], ['email.exists' => "Email is not registered!"]);

         // If validation fails, return error response
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
         // Attempt to log the user in with credentials
         $email = $request->email;  // Extract email and password
         $user = User::where('email', $email)->first();
         Auth::loginUsingId($user->id);
         if (!Auth::check()) {
             return response()->json([
                 'success' => false,
                 'errors' => 'Please SignUp first.',
             ], 422);  // Return Unauthorized status
         }

         // If it's a web login and web access is restricted
         if (isset($request->is_web) && $request->is_web == 1 && auth()->user()->organization_id == null) {
             return response()->json([
                 'errors' => ["As an individual account holder, web access is currently unavailable. Please use our mobile application for all services."]
             ], 422);
         }
          // If it's a web login and web access is restricted
          if (auth()->user()->status == 0) {
            return response()->json([
                'errors' => ["You don't have access for login, your organization head."]
            ], 422);
        }
        // Update last login time and IP address
        auth()->user()->last_login_date = now()->toDateTimeString();
        auth()->user()->last_device  = $request->device_name ?? $request->ip();
        auth()->user()->update();
         // Generate access token upon successful login
        $success['token'] = auth()->user()->createToken('authToken')->accessToken;
        $success['user'] = auth()->user() ?? [];
        if (auth()->user()->organization_id!= null)
        {
            $success['organization'] = auth()->user()->organization ?? [];
            $success['organization']['plan_name'] = SubscriptionPlan::where("plan_product_id",$success['organization']->plan_id)->first()->name ?? "";

            $success['user_id'] = auth()->user()->id ?? "";
            $user = auth()->user();
            $roleId = $user->roles->pluck('id')->first();
            if ($roleId !=null) {
                $role = Role::find($roleId)->first();
                $success['permissions']  = $role->permissions()->pluck('name')->toArray() ?? [];
            }else{
                $success['permissions']  = [];
            }
        }
        return response()->json(['success' => $success], 200);
     }
      public function logout(Request $request)
     {
         $request->user()->tokens()->delete();

         return response()->json([
             'message' => 'Logged out successfully'
         ]);
     }
      public function profileedit(Request $request){
        //validator place
        $user = auth()->user();
        $users = User::find($user->id);
        // If the email has changed, check if it already exists in the database
        if ($request->email != $users->email) {
            $emailExists = User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'message' => 'The email address is already in use by another user.'
                ], 422);
            }else {
            $user->status =0;
            $user->email_verified_at=null;
            }
        }
        if ($request->hasFile('image')) {
            $users->image = Helper::uploader($request,'image', 'uploaded/profile_image');
        }
        $users->name = $request->name;
        $users->email = $request->email;
        $users->phone = $request->phone;
        $users->save();
        $responseData['success'] =
                [
                "user" => $users
                ];


        return response()->json($responseData);


      }


}
