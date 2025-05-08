<?php

namespace App\Http\Controllers;

use App\Mail\AuthCodeMail;
use App\Mail\ForgottenPasswordMail;
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
        //extract matric number from email
        $email = request('email');
        $matricNumber = explode('.', explode('@', $email)[0])[1];
        $pic = 'https://api.dicebear.com/9.x/pixel-art/png?seed=' . request('name');
        $authCode = random_int(100000, 999999);
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'matric_no' => $matricNumber,
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
            return response()->json(['error' => 'Failed to send the authentication email. Please try again.', 'details' => $e->getMessage()], 500);
        }
        return response()->json(['user' => $user], 201);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|integer|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP.'], 422);
        }

        if ($user->otp_expiry_at < now()) {
            return response()->json(['error' => 'OTP has expired.'], 422);
        }

        $user->update(['otp' => null, 'otp_expiry_at' => null, 'email_verified_at' => now(), 'is_verified' => true]);

        return response()->json(['message' => 'Account verified successfully.']);
    }

    public function forgottenPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $authCode = random_int(100000, 999999);
        $user->update(['otp' => $authCode, 'otp_expiry_at' => now()->addMinutes(5)]);

        try {
            Mail::to($user->email)->send(new ForgottenPasswordMail($authCode));
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send the password reset email. Please try again.'], 500);
        }
        return response()->json(['message' => 'Password reset code sent to your email.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|integer|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP.'], 422);
        }

        if ($user->otp_expiry_at < now()) {
            return response()->json(['error' => 'OTP has expired.'], 422);
        }

        $user->update(['password' => Hash::make($request->password), 'otp' => null, 'otp_expiry_at' => null]);

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function login(Request $request)
    {
        // data validation
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);
        // check if user exists
        $user = User::where("email", $request->email)->first();
        if (empty($user)) {
            return response()->json([
                "status" => false,
                "message" => "User not found"
            ]);
        }
        if ($user->role_id != 4) {
            return response()->json([
                "status" => false,
                "message" => "You are not allowed on this route"
            ]);
        }
        // check if user is verified
        if ($user->is_verified == false) {
            return response()->json([
                "status" => false,
                "message" => "Account not verified"
            ]);
        }

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
        //with user role
        $user = User::with('role')->find(auth()->id());

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "avatar" => $user->avatar,
                "role" => $user->role?->name,
                "role_id" => $user->role_id,
                "is_verified" => $user->is_verified,
                "created_at" => $user->created_at,
            ]
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

    //delete user account
    public function deleteAccount($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "User not found"
            ]);
        }
        $user->delete();
        return response()->json([
            "status" => true,
            "message" => "User account deleted successfully"
        ]);
    }
}
