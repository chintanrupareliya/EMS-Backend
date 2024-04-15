<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

require_once app_path('Http/Helpers/APIResponse.php');

class AuthController extends Controller
{
    public function createUser(Request $request)
    {

            $validator=$this->validate($request, [
                'first_name'=> 'required|string',
                'last_name'=> 'required|string',
                'email'=> 'required|email|unique:users',
                'password'=> 'required|string|min:6',
            ]);

            $user = User::create([
                'first_name'=>$validator["first_name"],
                'last_name'=> $validator["last_name"],
                'email'=> $validator["email"],
                'password'=> Hash::make($validator["password"]),
            ]);

            return response()->json([
                'message' => 'User Created Successfully',
                'data' => $user
            ], 200);
    }


    public function loginUser(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|exists:users',
            'password' => 'required|min:6|string',
        ], [
            'email.required'    => 'The email is required.',
            'email.email'       => 'Please enter a valid email address.',
            'email.exists'      => 'The specified email does not exist',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'User Logged In Successfully',
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }
    public function getUserByToken(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'message' => 'User details retrieved successfully',
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }

    // Forgot password, send reset password link to email


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        try {
            $existingRecord = PasswordReset::where('email', $user->email)->first();

            if ($existingRecord) {
                return response()->json(['message' => 'Password reset link already sent. Please check your email.'], 400);
            }

            $token = Str::random(60);

            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
            ]);

            $resetLink = config('constant.frontend_url') . config('constant.reset_password_url') . $token;
            Mail::to($user['email'])->send(new ResetPasswordMail($resetLink,$user['email']));

            return response()->json(['message' => 'Password reset token Link to your email.'], 200);
        } catch (\Exception $e) {

            return response()->json(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()], 500);
        }
    }



public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'password' => 'required|confirmed|min:8',
    ]);

    try {
        $passwordReset = PasswordReset::where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();


        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }


        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json(['message' => 'Password reset successfully.'], 200);
    } catch (\Exception $e) {

        return response()->json(['message' => 'An unexpected error occurred.'], 500);
    }
}


    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'old_password' => 'required',
                'password' => 'required|confirmed|min:8',
            ]);

            $user = auth()->user();


            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'The provided old password is incorrect.'], 422);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Password changed successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while changing the password.'], 500);
        }
    }

}
