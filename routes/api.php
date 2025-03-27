<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\ReplyController;
use App\Http\Controllers\API\LikeController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\CodeCheckController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Controllers\API\MarkerController;
use App\Http\Controllers\API\MarkerCommentController;
use App\Http\Controllers\API\MarkerTodoController;
use App\Http\Controllers\API\MarkerNoteController;
use App\Http\Controllers\API\MarkerLinkController;
use App\Http\Controllers\API\HazardController;
use App\Http\Controllers\API\RegistrationCheckController;
use App\Http\Controllers\API\OrganizationController;
use App\Http\Controllers\API\EmailCheckController;
use App\Http\Controllers\API\SubscriptionPlanController;
use App\Http\Controllers\API\ChangePasswordController;
use App\Http\Controllers\API\GroupController;
use App\Http\Controllers\API\MemberController;
use App\Http\Controllers\API\GroupMemberController;
use App\Http\Controllers\API\NotificationController;

use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\UserRoleAssignmentController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('soical/login', [AuthController::class, 'social_login']);

Route::post('/customer/plan/reccuring/module', [SubscriptionPlanController::class, 'recurring_handleWebhook']);

Route::get('/posts/{post}', [PostController::class, 'show']);
Route::post('/posts/{post}', [PostController::class, 'update']);
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::get('posts/{post}/comments',[CommentController::class,'index'])->name('comments.index');
Route::get('comments/{comment}/replies',[ReplyController::class,'index'])->name('replies.index');
Route::post('check/new/email',  EmailCheckController::class);
Route::post('password/email',  ForgotPasswordController::class);
Route::post('password/code/check',CodeCheckController::class);
Route::post('email/code/check',RegistrationCheckController::class);
Route::post('password/reset', ResetPasswordController::class);
Route::post('create-payment-intent', [SubscriptionPlanController::class, 'createPaymentIntent']);
Route::post('create-payment', [SubscriptionPlanController::class, 'createPayment']);
Route::prefix('organization')->group(function ()
{
    Route::post('registration', [OrganizationController::class, 'registration']);
});
Route::post('contact/sales', [TicketController::class, 'storesalecontact']);
Route::middleware('auth:api')->group( function () {

    Route::post('support/ticket', [TicketController::class, 'storedata']);
    Route::post('Apilogout', [AuthController::class, 'logout']);
    Route::post('posts/{post}/comments', [CommentController::class,'store'])->name('comments.store');
    Route::get('posts/{post}/comments/{comment}/edit', [CommentController::class,'edit'])->name('comments.edit');
    Route::put('posts/comments/{post}/{comment}', [CommentController::class,'update'])->name('comments.update');
    Route::delete('posts/comments/{post}/{comment}',[CommentController::class,'destroy'])->name('comments.destroy');

    Route::post('updateProfile', [AuthController::class, 'profileedit']);
    Route::post('password/update', ChangePasswordController::class);

    // Roles
    Route::resource('roles', RoleController::class);

    // Permissions
    Route::resource('permissions', PermissionController::class);

    // User roles and permissions
    Route::post('user/assign-role', [UserRoleAssignmentController::class, 'assignRole']);
    Route::post('user/update-role-permissions', [UserRoleAssignmentController::class, 'updateRolePermissions']);
    Route::get('user/roles/{roleId}/permissions', [UserRoleAssignmentController::class, 'getRoleWithPermissions']);

    // Route::post('user/{userId}/assign-role', [UserRoleAssignmentController::class, 'assignRole']);
    // Route::post('user/{userId}/remove-role', [UserRoleAssignmentController::class, 'removeRole']);
    // Route::post('user/{userId}/assign-permission', [UserRoleAssignmentController::class, 'assignPermission']);
    // Route::post('user/{userId}/remove-permission', [UserRoleAssignmentController::class, 'removePermission']);

        // Index (List all replies)
     //   Route::get('comments/{comment}/replies', [ReplyController::class, 'index']);

        // Create (Show form to create a reply)
        Route::get('comments/{comment}/replies/create', [ReplyController::class, 'create']);

        // Store (Save a new reply)
        Route::post('comments/replies/{comment}', [ReplyController::class, 'store']);

        // Edit (Show form to edit a reply)
      //  Route::get('comments/{comment}/replies/{reply}/edit', [ReplyController::class, 'edit']);

        // Update (Save changes to an existing reply)
       // Route::put('comments/{comment}/replies/{reply}', [ReplyController::class, 'update']);

        // Destroy (Delete a reply)
        Route::delete('comments/replies/{comment}/{reply}', [ReplyController::class, 'destroy']);



        //Like Routes

        Route::post('posts/like/{post}', [LikeController::class, 'likePost']);
        Route::delete('posts/like/{post}', [LikeController::class, 'unlikePost']);

    //Route::resource('posts.comments',CommentController::class)->except(['show','index']);
    //Route::resource('comments.replies', ReplyController::class)->except(['show']);;

// Create a marker
Route::post('markers', [MarkerController::class, 'store']);
Route::post('import/markers/data', [MarkerController::class, 'importMarker']);
Route::post('import/markers/update', [MarkerController::class, 'tempDataUpdate']);
Route::post('import/markers/show', [MarkerController::class, 'importMarkerShow']);

// Retrieve all markers
Route::get('markerslist', [MarkerController::class, 'index']);
// Retrieve all markers
Route::post('group/markers/list', [MarkerController::class, 'group_markers']);
Route::post('user/markers/list', [MarkerController::class, 'user_markers']);

// Retrieve a specific marker
Route::get('markers/{id}', [MarkerController::class, 'show']);

// Update a marker
Route::put('markers/{marker}', [MarkerController::class, 'update']);

// Delete a marker
Route::delete('markers/{marker}', [MarkerController::class, 'destroy']);


//Marker Comments
Route::post('markers/comments/{markerid}', [MarkerCommentController::class, 'store']);
Route::get('markers/{marker}/comments', [MarkerCommentController::class, 'showComments']);
Route::post('markers/{marker}/comments/{comment}', [MarkerCommentController::class, 'update']);
Route::delete('markers/{marker}/comments/{comment}', [MarkerCommentController::class, 'destroy']);


//Marker Todo
Route::post('/marker_todos', [MarkerTodoController::class, 'store']);
//Marker Note
Route::post('/marker_note', [MarkerNoteController::class, 'store']);
//Marker Link
Route::post('/marker_link', [MarkerLinkController::class, 'store']);

//Marker hazards
Route::post('/marker_hazards', [HazardController::class, 'saveHazard']);

//hazards
Route::post('/hazards', [HazardController::class, 'store']);

Route::get('/hazards-list', [HazardController::class, 'index']);

Route::post('hazards/{id}', [HazardController::class, 'update']);
//Marker Todo
Route::post('/saveoption', [MarkerController::class, 'saveoption']);

Route::post('/getalloption', [MarkerController::class, 'getalloption']);

Route::post('/updateoption', [MarkerController::class, 'updateoption']);
Route::post('/marker/deleted', [MarkerController::class, 'deleted_index']);
Route::get('marker/img/{id}', [MarkerController::class, 'marker_imgs']);
Route::post('upload/marker/img', [MarkerController::class, 'upload_marker_imgs']);
Route::delete('delete/marker/img/{id}', [MarkerController::class, 'delete_marker_img']);
Route::post('/marker/restore/{id}', [MarkerController::class, 'restore']); // Delete a member by ID
Route::post('/marker/hard/delete/{id}', [MarkerController::class, 'hard_destroy']); // Delete a member by ID


// MemberController routes
Route::prefix('members')->group(function () {
    Route::post('/', [MemberController::class, 'index']);
    Route::post('/status/update', [MemberController::class, 'updateUserStatus']);        // Fetch all members
    Route::post('show/{id}', [MemberController::class, 'show']);      // Fetch a specific member by ID
    Route::post('group/get', [MemberController::class, 'getMemberGroup']);
    // Route::post('store', [MemberController::class, 'store']);
    Route::post('store', [MemberController::class, 'store']);
    Route::post('update/{id}', [MemberController::class, 'update']);    // Update a member by ID
    Route::post('delete/{id}', [MemberController::class, 'destroy']); // Delete a member by ID
    Route::post('/deleted', [MemberController::class, 'deleted_index']);
    Route::post('restore/{id}', [MemberController::class, 'restore']); // Delete a member by ID
    Route::post('hard/delete/{id}', [MemberController::class, 'hard_destroy']); // Delete a member by ID
});

// GroupController routes
Route::prefix('groups')->group(function () {
    Route::post('/',  [GroupController::class, 'index']);         // Fetch all groups
    Route::post('show/{id}',[GroupController::class, 'show']);       // Fetch a specific group by ID
    Route::post('store', [GroupController::class, 'store']);        // Create a new group
    Route::post('update/{id}',  [GroupController::class, 'update']);     // Update a group by ID
    Route::post('delete/{id}', [GroupController::class, 'destroy']); // Delete a group by ID
    Route::post('{id}/reset-code', [GroupController ::class, 'resetCode']);
    Route::get('{groupId}/members', [GroupMemberController::class, 'index']);
    Route::post('join/request', [GroupMemberController::class, 'requestToJoin']);
    Route::post('{groupId}/members/{userId}/approve', [GroupMemberController::class, 'approveJoinRequest']);
    Route::post('{groupId}/members/{userId}/deny', [GroupMemberController::class, 'denyJoinRequest']);
    Route::post('{groupId}/members/add', [GroupMemberController::class, 'addMembers']);
    Route::post('{groupId}/invite', [GroupMemberController::class, 'generateInviteLink']);
    Route::post('code/{groupId}/send', [GroupMemberController::class, 'generateInviteCode']);
    Route::post('members/make-admin', [GroupMemberController::class, 'makeAdmin']);
    Route::post('members/remove-admin', [GroupMemberController::class, 'removeAdmin']);
    Route::post('remove/members/', [GroupMemberController::class, 'removeMember']);
    Route::post('leave/members', [GroupMemberController::class, 'removeMember']);
    Route::post('organization/members', [GroupMemberController::class, 'GetAllGroupMembers']);
    Route::post('members/update/status', [GroupMemberController::class, 'updateJoinRequests']);

});

Route::post('organization/update/{id}', [OrganizationController::class, 'updateOrganization']);
Route::post('organization/transaction/history', [OrganizationController::class, 'transaction_history']);
Route::post('organization/dashboard', [OrganizationController::class, 'dashboard']);

//User List
Route::get('users', [MarkerCommentController::class, 'users']);

Route::post('sync/plan', [SubscriptionPlanController::class, 'plan']);
Route::post('customer/subscribe', [SubscriptionPlanController::class, 'subscribe']);
Route::post('customer/subscribe/buy', [SubscriptionPlanController::class, 'buy_subscription']);

Route::prefix('notifications')->group(function () {
    Route::post('/update-fcm-token-mobile',[NotificationController::class,'updateTokenMobile']);
    Route::post('/update-fcm-token-desktop',[NotificationController::class,'updateTokenDesktop']);
    Route::post('/single-mark-as-read',[NotificationController::class,'MarkNotificationAsRead']);
    Route::post('/mark-as-read',[NotificationController::class,'NotificationAsRead']);
    Route::post('/get-unread-notifications', [NotificationController::class,'getUnreadNotifications']);
    Route::post('/get-read-notifications',[NotificationController::class,'getReadNotifications']);
    Route::post('/delete-notification',[NotificationController::class,'destroy']);
    Route::post('/all-clear-notification',[NotificationController::class,'clearAll']);
});

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
   // return $request->user();
   // $success['token'] = auth()->user()->createToken('authToken')->accessToken;
    $success['user'] =$request->user();;
    return response()->json(['success' => $success])->setStatusCode(200);
});
