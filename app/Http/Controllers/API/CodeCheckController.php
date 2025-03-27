<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use App\Models\User;
use Hash;

class CodeCheckController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
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
           // return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);
        $data=$request->all();
        $password=$data['password'];
       // dd($password);

        // update user password
        $user->update(['password'=>Hash::make($password),'email_verified_at' => now()]);

        // delete current code
        $passwordReset->delete();
        return response()->json([
            'success' => true,
            'user' => $user,
            'message' =>  'password has been successfully reset'
        ], 200);
       // return response(['message' =>'password has been successfully reset'], 200);
    }
}
