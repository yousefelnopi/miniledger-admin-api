<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /**
     * Login Process
     */



    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_ADMIN,
            'is_active' => true
        ])) {
            return $this->errorResponse('Invalid credentials', null, 401);
        }

        $user = Auth::user();


        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // استخدام دالة النجاح من الـ Trait
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'login successfully', 200);
    }
}
