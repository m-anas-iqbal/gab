<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use App\Models\User;
use Hash;

class RegistrationCheckController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->validate([
            'code' => 'required|exists:reset_code_passwords',
        ]);

        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);
        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response()->json([
                'success' => false,
                'errors' =>  trans('passwords.code_is_expire')
            ], 422);
        }
        $user = User::firstWhere('email', $passwordReset->email);
        if ($user) {
        $user->update(['email_verified_at' => now()]);
        }
        // // delete current code
        $passwordReset->delete();
        return response()->json([
            'success' => true,
            'message' =>  'Email has been verified'
        ], 200);
       // return response(['message' =>'password has been successfully reset'], 200);
    }
}
