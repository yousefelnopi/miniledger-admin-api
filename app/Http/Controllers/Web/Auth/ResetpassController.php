<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\ResetpassRequest;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetpassController extends Controller
{
    /**
     * 1. Change password from inside the app (For authenticated users)
     * Route: POST /api/change-password
     * Middleware: auth:sanctum
     */
    public function changePassword(ResetpassRequest $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Update user's password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Revoke all tokens to logout from other devices (Extra security)
        $user->tokens()->delete();

        return $this->successResponse(null, 'Password changed successfully. Please login again.');
    }

    /**
     * 2. Send password reset link to user's email (Forgot Password)
     * Route: POST /api/forget-password
     * Middleware: guest
     */
    public function forgetPassword(Request $request)
    {
        // 1. Validate email
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // 2. Generate a strong random token
        $token = Str::random(64);

        // 3. Insert or update the token in the database (faster than delete & insert)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        // 4. Prepare the frontend blade URL
        $resetLink = url('/reset-password-view?' . http_build_query([
            'token' => $token,
            'email' => $request->email
        ]));
        // 5. Send the premium email
        Mail::to($request->email)->send(new ResetPasswordMail($resetLink));

        return $this->successResponse(null, 'Password reset link has been sent to your email.');
    }

    /**
     *  Reset password from the link sent to the user's email
     */
    public function resetForgottenPassword(Request $request)
    {
        // 1. Validate data coming from the Blade view
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Find the reset request and verify the token
        $resetRequest = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRequest || !Hash::check($request->token, $resetRequest->token)) {
            return $this->errorResponse('Invalid or expired password reset link.', null, 400);
        }

        // 3. Check if the token is older than 10 minutes
        if (Carbon::parse($resetRequest->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('The reset link has expired. Please request a new one.', null, 400);
        }

        // 4. Find the user and update the password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // 5. Revoke all existing tokens (Logout from all devices)
        $user->tokens()->delete();

        // 6. Delete the reset token so it can't be used again
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Password reset successfully. You can now login.');
    }
}
