<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;


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



            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

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
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $token = Password::getRepository()->create($user);

        $resetLink = 'http://localhost:5173/reset-password/' . $token;

        Mail::to($user->email)->send(new ResetPasswordMail($resetLink));

        return response()->json(['message' => 'Password reset token sent to your email.'], 200);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.'], 200);
        } else {
            return response()->json(['message' => 'Invalid token or email provided.'], 400);
        }
    }
}
