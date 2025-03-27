<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeResetPassword;
use App\Mail\SendCodeEmailCheck;
class EmailCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        if(isset($request->exist) && $request->exist == 1 ){
             $data = $request->validate([
                'email' =>"required|email|unique:users,email,{$request->id},id",
            ]);
            // dd($request->all());
        }else {
            // dd($request->all());
            $data = $request->validate([
                'email' => 'required|email|unique:users,email',
            ]);
        }

        ResetCodePassword::where('email', $request->email)->delete();
        $data['code'] = mt_rand(100000, 999999);
        $codeData = ResetCodePassword::create($data);
        Mail::to($request->email)->send(new SendCodeEmailCheck($codeData->code));
        return response()->json([
            'success' => true,
            'message' =>   trans('passwords.otp')
        ], 200);
    }
}
