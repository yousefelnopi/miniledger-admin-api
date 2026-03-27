<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\CashierLoginRequest;
use App\Http\Resources\Api\Auth\CasherLoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasherAuthController extends Controller
{
    /**
     * Handle cashier login
     */
    public function login(CashierLoginRequest $request)
    {
        $data = $request->validated();

        // Verify password, role, and active status in one step
        if (!Auth::attempt([
            'email'     => $data['email'],
            'password'  => $data['password'],
            'role'      => User::ROLE_CASHIER,
            'is_active' => true
        ])) {
            return $this->errorResponse('Invalid credentials or account inactive', null, 401);
        }

        $user = Auth::user();

        // Delete old tokens to prevent double login
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Update last seen timestamp
        $user->update([
            'last_seen_at' => now()
        ]);

        return $this->successResponse(
            new CasherLoginResource($user, $token),
            'Logged in successfully'
        );
    }

    /**
     * Handle cashier logout
     */
    public function logout(Request $request)
    {
        // Delete current token only
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout successfully');
    }
}
