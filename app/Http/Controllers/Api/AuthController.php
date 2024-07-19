<?php
// controller for authentication
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



class AuthController extends Controller
{

    /**
     * Display a listing of the users.
     *
     * @method POST
     * @author Chintan Rupareliya
     * @route /auth/register
     * @authentication no
     * @middleware no
     * @return \Illuminate\Http\Response
     */
    //function for create user as candidate
    public function createUser(Request $request)
    {
        $validator = $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed', //send "password_confirmation" with api request
        ]);


        $password = $validator['password'];
        unset($validator['password']);

        $user = User::create([
            'first_name' => $validator["first_name"],
            'last_name' => $validator["last_name"],
            'email' => $validator["email"],
            'password' => Hash::make($password),

        ]);

        return ok(
            'User Created Successfully',
            ['user' => $user, 'token' => $user->createToken("API TOKEN")->plainTextToken],
            200
        );
    }


    /**
     * Log in a user and send authentication token.
     *
     * @method POST
     * @author Chintan Rupareliya
     * @route /auth/login
     * @authentication no
     * @middleware no
     * @return \Illuminate\Http\Response
     */

    //login user and send authentication token
    public function loginUser(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6|string',
        ], [
            'email.required' => 'The email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'The specified email does not exist',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return error("Invalid email or password", [], "unauthenticated");
        }

        return ok("successfully login", [
            'status' => true,
            'message' => 'User Logged In Successfully',
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    /**
     * Get the user details by authentication token.
     *
     * @method GET
     * @route /auth/user
     * @authentication yes
     * @middleware auth:sanctum
     * @return \Illuminate\Http\Response
     */

    //get the user by auth token
    public function getUserByToken(Request $request)
    {
        try {
            $user = $request->user();

            return ok("success", [
                'status' => true,
                'message' => 'User details retrieved successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return error("User Not Found", [], 'notfound');
        }
    }

    /**
     * Log out the user and delete authentication tokens.
     *
     * @method POST
     * @route /auth/logout
     * @authentication yes
     * @middleware auth:sanctum
     * @return \Illuminate\Http\Response
     */

    // logout the user and remove auth tken for table
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ok("User logged out successfully", [], 200);
    }

    /**
     * Send a reset password link to the user's email.
     *
     * @method POST
     * @route /auth/forgot-password
     * @authentication no
     * @middleware no
     * @return \Illuminate\Http\Response
     */
    // Forgot password, send reset password link to email
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return error('User not found.', [], "notfound");
        }

        try {
            $existingRecord = PasswordReset::where('email', $user->email)->first();

            if ($existingRecord) {
                return error('Password reset link already sent. Please check your email.', []);
            }

            $token = Str::random(60);

            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
            ]);

            $resetLink = config('constant.frontend_url') . config('constant.reset_password_url') . $token;
            //sending email
            Mail::to($user['email'])->send(new ResetPasswordMail($resetLink, $user['email']));

            return ok('Password reset token Link to your email.', [], 200);
        } catch (\Exception $e) {
            return error('An unexpected error occurred.', [$e->getMessage()]);
        }
    }

    /**
     * Reset the user's password using a reset password token.
     *
     * @method POST
     * @route /auth/reset-password
     * @authentication no
     * @middleware no
     * @return \Illuminate\Http\Response
     */

    //reset password using password reset token and store token in password_reset_token table with associated email
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        try {
            $passwordReset = PasswordReset::where('token', $request->token)->first();

            if (!$passwordReset) {
                return error('Invalid or expired token.', [], 'notfound');
            }

            $user = User::where('email', $passwordReset->email)->first();


            if (!$user) {
                return error('User not found.', 404, 'notfound');
            }


            $user->password = Hash::make($request->password);
            $user->save();

            $passwordReset->delete();

            return ok('Password reset successfully please login with new password.', [], 200);
        } catch (\Exception $e) {

            return error('An unexpected error occurred.', []);
        }
    }

    /**
     * Change the password for the authenticated user.
     *
     * @method POST
     * @route /auth/change-password
     * @authentication yes
     * @middleware auth:sanctum
     * @return \Illuminate\Http\Response
     */

    //this for authenticated user that want to change the password
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'old_password' => 'required',
                'password' => 'required|confirmed|min:8',
            ]);

            $user = auth()->user();


            if (!Hash::check($request->old_password, $user->password)) {
                return error('The provided old password is incorrect.', [], 'validation');
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return ok('Password changed successfully.', [], 200);
        } catch (\Exception $e) {
            return error('An error occurred while changing the password.');
        }
    }

}
