<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Find the 'user' role
            $userRole = Role::where('name', 'user')->first();
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $userRole->id, // Assign the user role ID
            ]);

            $token = $user->createToken('auth_token')->accessToken;

            return response()->json(['token' => $token], 200);
        } catch (\Exception $e) {
            \Log::error('Registration Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    


    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('Personal Access Token')->accessToken;

    return response()->json([
        'token' => $token,
        'role_id' => $user->role_id, 
    ]);
}


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email',
        ]);

        // Send the password reset link
        $response = Password::sendResetLink($request->only('email'));

        // Handle the response
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email address.']);
        } else {
            return response()->json(['error' => 'Unable to send password reset link.'], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Attempt to reset the password
        $response = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        // Handle the response
        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully.']);
        } else {
            return response()->json(['error' => 'Unable to reset password.'], 500);
        }
    }
}
