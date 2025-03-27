<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class ResetPasswordController extends Controller
{
    /**
     * Change the password (Setp 3)
     *
     * @param  mixed $request
     * @return void
     */
    public function __invoke(ResetPasswordRequest $request)
    {
        // Find the reset code in the database
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // Check if the reset code exists and if it's expired
        if (!$passwordReset || $passwordReset->isExpire()) {
            return response()->json(['message' => trans('passwords.code_is_expire')], 422);
        }

        // Find the user by the email associated with the reset code
        $user = User::firstWhere('email', $passwordReset->email);

        if (!$user) {
            return response()->json(['message' => trans('passwords.user_not_found')], 404);
        }

        // Update the user's password securely
        $user->update([
            'password' => Hash::make($request->password),'email_verified_at' => now()
        ]);
        // Delete the password reset code after successful reset
        $passwordReset->delete();

        return response()->json(['message' => trans('site.password_has_been_successfully_reset')], 200);
    }

}
