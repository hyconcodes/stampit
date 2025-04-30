<?php

namespace App\Http\Controllers;

use App\Mail\AuthCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register()
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z]+\.\d+@bouesti\.edu\.ng$/'
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.regex' => 'Not a valid school email.',
        ]);
        $pic = 'https://api.dicebear.com/9.x/pixel-art/png?seed=' . request('name');
        $authCode = random_int(1000, 9999);
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'avatar' => $pic,
            'password' => Hash::make(request('password')),
            'otp' => $authCode,
            'otp_expiry_at' => now()->addMinutes(5),
            'role_id' => 4, // 4 is the role ID for students
        ]);
        try {
            Mail::to(request('email'))->send(new AuthCodeMail($authCode));
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send the authentication email. Please try again.' . $e->getMessage()], 500);
        }
        return response()->json(['user' => $user], 201);
    }



    public function login(Request $request)
    {

        // data validation
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        // JWTAuth
        $token = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);

        if (!empty($token)) {

            return response()->json([
                "status" => true,
                "message" => "User logged in succcessfully",
                "token" => $token
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid details"
        ]);
    }

    public function profile()
    {
        $userdata = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userdata
        ]);
    }

    // To generate refresh token value
    public function refreshToken()
    {

        $newToken = JWTAuth::parseToken()->refresh();

        return response()->json([
            "status" => true,
            "message" => "New access token",
            "token" => $newToken
        ]);
    }

    // User Logout (GET)
    public function logout()
    {

        auth()->logout();

        return response()->json([
            "status" => true,
            "message" => "User logged out successfully"
        ]);
    }
}
