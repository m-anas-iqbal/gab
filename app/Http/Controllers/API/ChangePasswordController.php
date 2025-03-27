<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
class ChangePasswordController extends Controller
{
    /**
     * Change the password (Setp 3)
     *
     * @param  mixed $request
     * @return void
     */
    public function __invoke(Request $request)
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed',  // 'confirmed' ensures password_confirmation matches
        ]);

        // If validation fails, return a response with errors
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Update the user's password, hashing it
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Return a success message
        return response()->json("Password Updated Successfully!", 200);
    }

}
