<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            $this->validate($request, [
                'name'=> 'required|string',
                'email'=> 'required|email|unique:users',
                'password'=> 'required|string|min:6',
            ]);
    
            $user = User::create([
                'name'=> $request->name,
                'email'=> $request->email,
                'password'=> Hash::make($request->password),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    public function loginUser(Request $request)
    {
        try {
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
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }  

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); 
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
        