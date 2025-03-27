<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeResetPassword;
use App\Mail\SendCodeEmailCheck;
class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ],['email.exists'=>"The email you entered does not exist."]);
        // Delete all old code that user send before.
        ResetCodePassword::where('email', $request->email)->delete();
        // Generate random code
        $data['code'] = mt_rand(100000, 999999);
        // Create a new code
        $codeData = ResetCodePassword::create($data);
        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));
        return response()->json([
            'success' => true,
            'message' =>   trans('passwords.sent')
        ], 200);
    }
}
