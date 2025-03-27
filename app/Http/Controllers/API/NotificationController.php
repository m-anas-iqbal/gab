<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
// use App\Services\FirebaseService;
// use Kreait\Firebase\Exception\FirebaseException;
// use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Auth;
use function auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    // protected $firebaseService;

    // public function __construct(FirebaseService $firebaseService)
    // {
    //     $this->firebaseService = $firebaseService;
    // }

    // public function sendNotification(Request $request)
    // {
    //     $validated = $request->validate([
    //         'device_token' => 'required',
    //         'title' => 'required',
    //         'body' => 'required',
    //     ]);
    //     try {
    //         $response = $this->firebaseService->sendNotification(
    //             $validated['device_token'],
    //             $validated['title'],
    //             $validated['body']
    //         );

    //         return response()->json(['status' => 'Notification sent!', 'response' => $response]);
    //     } catch (FirebaseException $e) {
    //         return response()->json(['status' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'An error occurred', 'error' => $e->getMessage()], 500);
    //     }

    // }

    public function updateTokenMobile(Request $request)
    {
        $validated = $request->validate([
            'fcmtoken' => 'required|string',
        ]);
        $user = Auth::user();
        if (empty($user->fcmtoken)) {
            $user->mobile_fcm = $validated['fcmtoken'];
            $message = 'FCM token added successfully!';
        } else {
            $user->mobile_fcm = $validated['fcmtoken'];
            $message = 'FCM token updated successfully!';
        }

        $user->save();
        return response()->json(['status' => $message]);
    }
    public function updateTokenDesktop(Request $request)
    {
        $validated = $request->validate([
            'fcmtoken' => 'required|string',
        ]);
        $user = Auth::user();
        if (empty($user->fcmtoken)) {
            $user->desktop_fcm = $validated['fcmtoken'];
            $message = 'FCM token added successfully!';
        } else {
            $user->desktop_fcm = $validated['fcmtoken'];
            $message = 'FCM token updated successfully!';
        }

        $user->save();
        return response()->json(['status' => $message]);
    }

    public function NotificationAsRead(Request $request)
    {
        // $userId = $request->user_id;


        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' =>'success',
            'message' => __('All notifications marked as read.')
        ]);

    }
    public function MarkNotificationAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
        ]);

        $notificationId = $request->notification_id;

        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();
        if (!$notification) {
            return response()->json(['error' => __('Notification not found or does not belong to the user.')], 404);
        }

        if ($notification->read_at !== null) {
            return response()->json(['error' => __('Notification already marked as read.')], 400);
        }

        $notification->read_at = Carbon::now();
        $notification->save();

        return response()->json([
                        'status' => 'success',
                        'message' => __('Notification marked as read.')
        ]);
    }

    public function getUnreadNotifications(Request $request)
    {
        $userId = $request->user_id;

        // Fetch unread notifications for the authenticated user
        $unreadNotifications = auth()->user()->notifications()
            ->whereNull('read_at')
            ->get();

        // Check if there are any unread notifications
        if ($unreadNotifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'error' => __('No unread notifications found.')
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $unreadNotifications
        ], 200);
    }



    public function getReadNotifications(Request $request)
    {
        // $perPage = $request->get('per_page', 10);

        $readNotifications = auth()->user()->notifications()
        ->whereNotNull('read_at')->get();
        // ->paginate($perPage);

        if ($readNotifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'error' => __('No read notifications found.')
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $readNotifications
        ], 200);
    }

    public function destroy(Request $request)
	{
        $id =$request->notification_id;

		$notification = auth()->user()->notifications()->find($id);

		if ($notification) {
			$notification->delete();
			return response()->json(['status' => 'success', 'message' => 'Notification removed successfully.']);
		}

		return response()->json(['status' => 'error', 'message' => 'Notification not found.'], 422);
	}

	public function clearAll()
	{
		auth()->user()->notifications()->delete();
		return response()->json(['status' => 'success', 'message' => 'All notification has been cleared successfully.']);
	}

}
