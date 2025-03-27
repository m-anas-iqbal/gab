<?php

namespace App\Helpers;

use Carbon\Carbon;
use Google\Client as GoogleClient;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DynamicNotification;
use Illuminate\Support\Facades\Log;

class Helper
{
    public static function removeTokensFromTopic($fcmTokens, $topic)
    {
        $projectId = config('0');
        $credentialsFilePath = config('services.fcm.file');
        if (!file_exists($credentialsFilePath)) {
            return ['success' => false, 'message' => 'FCM credentials file not found'];
        }
        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $accessToken = $token['access_token'] ?? null;

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Unable to fetch access token'];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $data = [
            "to" => "/topics/$topic",
            "registration_tokens" => $fcmTokens, // Tokens to remove from the topic
        ];

        $payload = json_encode($data);

        // Send the request to remove the tokens from the topic
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1:batchRemove");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Error removing tokens from topic: ' . $err];
        }

        return ['success' => true, 'message' => 'Tokens removed from topic successfully', 'response' => json_decode($response, true)];
    }
    public static function subscribeTokensToTopic($fcmTokens, $topic)
    {
        $projectId = config('services.fcm.project_id');
        $credentialsFilePath = config('services.fcm.file');

        if (!file_exists($credentialsFilePath)) {
            return ['success' => false, 'message' => 'FCM credentials file not found'];
        }

        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $accessToken = $token['access_token'] ?? null;

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Unable to fetch access token'];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $data = [
            "to" => "/topics/$topic",
            "registration_tokens" => $fcmTokens,
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1:batchAdd");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Error subscribing to topic: ' . $err];
        }

        return ['success' => true, 'message' => 'Tokens subscribed to topic successfully', 'response' => json_decode($response, true)];
    }
    public static function sendFcmNotificationToTopic($topic, $title, $description, $icon = null)
    {
        $projectId = config('services.fcm.project_id');
        $credentialsFilePath = config('services.fcm.file');

        if (!file_exists($credentialsFilePath)) {
            return ['success' => false, 'message' => 'FCM credentials file not found'];
        }

        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $accessToken = $token['access_token'] ?? null;

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Unable to fetch access token'];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $data = [
            "message" => [
                "topic" => $topic,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                    "image" => $icon,
                ],
            ],
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Error sending notification: ' . $err];
        }

        return ['success' => true, 'message' => 'Notification sent successfully', 'response' => json_decode($response, true)];
    }
    public static function sendFcmNotification($userIds, $title, $description, $icon = null,$userData = null)
    {
        // Fetch users with valid FCM tokens
        $users = User::whereIn('id', $userIds)->get();
        if ($users->isEmpty()) {
            return ['success' => false, 'message' => 'No users with valid FCM tokens'];
        }
        Notification::send($users, new DynamicNotification($title, $description, $icon,$userData));
        $desktop_fcm = $users->pluck('desktop_fcm')->toArray();
        $mobile_fcm = $users->pluck('mobile_fcm')->toArray();
        $fcmTokens = array_merge($desktop_fcm, $mobile_fcm);
        $fcmTokens = array_filter($fcmTokens);

        if (empty($fcmTokens)) {
            return ['success' => false, 'message' => 'No valid FCM tokens'];
        }

        // Load FCM credentials
        $projectId = config('services.fcm.project_id');
        $credentialsFilePath = config('services.fcm.file');
        if (!file_exists($credentialsFilePath)) {
            return ['success' => false, 'message' => 'FCM credentials file not found'];
        }

        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        if (!isset($token['access_token'])) {
            return ['success' => false, 'message' => 'Unable to fetch access token'];
        }

        $accessToken = $token['access_token'];

        // Prepare CURL headers
        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        // Initialize response tracking
        $successCount = 0;
        $failureCount = 0;
        $errorMessages = [];

        // Iterate through FCM tokens
        foreach ($fcmTokens as $fcmToken) {
            $data = [
                "message" => [
                    "token" => $fcmToken, // Single token per request
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                        "image" => $icon,
                    ],
                ],
            ];

            $payload = json_encode($data);

            // CURL request to FCM
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            // Handle FCM response for each token
            if ($err) {
                $failureCount++;
                $errorMessages[] = 'Curl Error: ' . $err;
            } else {
                $responseDecoded = json_decode($response, true);
                if (isset($responseDecoded['error'])) {
                    $failureCount++;
                    $errorMessages[] = 'FCM Error: ' . $responseDecoded['error']['message'] ?? 'Unknown error';
                } else {
                    $successCount++;
                }
            }
        }
        // Laravel Notifications (optional)
        // Notification::send($users, new DynamicNotification($title, $description, $icon));

    }

    public function sendFcmNotification1($users =[],$title,$description,$icon)
    {
        // Fetch users with valid FCM tokens
        $users = User::whereIn('id', $userIds)
            ->get();
        if ($users->isEmpty()) {
            return ['success' => false, 'message' => 'No users with valid FCM tokens'];
        }
        $desktop_fcm = $users->pluck('desktop_fcm')->toArray();
        $mobile_fcm = $users->pluck('mobile_fcm')->toArray();
        $fcmTokens =array_merge($desktop_fcm,$mobile_fcm);
        $fcmTokens = array_filter($fcmTokens);
        // dd($fcmTokens);
        // Load FCM credentials
        $projectId = config('services.fcm.project_id');
        $credentialsFilePath =config('services.fcm.file');
        if (!file_exists($credentialsFilePath)) {
            return ['success' => false, 'message' => 'FCM credentials file not found'];
        }
        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        if (!isset($token['access_token'])) {
            return ['success' => false, 'message' => 'Unable to fetch access token'];
        }

        $accessToken = $token['access_token'];

        // FCM payload
        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $data = [
            "message" => [
                "token" => $fcmTokens[1], // Multiple tokens
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                    "image" => $icon
                ]
            ]
        ];

        $payload = json_encode($data);

        // CURL request to FCM
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        // Handle FCM response
        if ($err) {
            return ['success' => false, 'message' => 'Curl Error: ' . $err];
        }

        $responseDecoded = json_decode($response, true);
        dd($users,$fcmTokens,$response);
        Log::info($fcmTokens);
        if (isset($responseDecoded['error'])) {
            return ['success' => false, 'message' => 'FCM Error', 'details' => $responseDecoded];
        }


        // Laravel Notifications
        Notification::send($users, new DynamicNotification($title, $description, $icon));

        // return ['success' => true, 'message' => 'Notifications sent successfully', 'response' => $responseDecoded];
    }
    function uploader($request, $fieldName,  $folder = 'uploaded/files',$index = null)
{
    ini_set('post_max_size', '20000M');
    ini_set('upload_max_filesize', '20000M');
    ini_set('max_execution_time', 20000);

    $uploadDirectory = public_path($folder);
    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }
    // Check if it's an array upload (multiple files)
    if ($index !== null) {
        $file = $request->file($fieldName)[$index] ?? null;
    } else {
        $file = $request->file($fieldName);
    }

// dd($file,$index != null,$index,$request->file($fieldName)[$index]);
    if ($file) {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');

        // Generate a unique file name
        $newFileName = sprintf('%s_%s.%s',
            str_replace([' ', '-'], '_', strtolower(pathinfo($originalName, PATHINFO_FILENAME))),
            $timestamp,
            $extension
        );
        $filePath = $folder . '/' . $newFileName;
        $saved = $file->move(public_path($folder), $newFileName);

        if (!$saved) {
            dd("File upload failed!", $filePath);
        }
        return $filePath;
    }

    return null;
}

}
