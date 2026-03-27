<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CasherController extends Controller
{
    /**
     * Get all cashiers
     */
    public function index()
    {
        $cashiers = User::where('role', User::ROLE_CASHIER)->get();

        return $this->successResponse($cashiers, 'Cashiers retrieved successfully');
    }

    /**
     * Show a specific cashier
     */
    public function show(User $user)
    {
        // Only allow viewing cashiers
        if ($user->role !== User::ROLE_CASHIER) {
            return $this->errorResponse('User is not a cashier', null, 403);
        }

        return $this->successResponse($user, 'Cashier retrieved successfully');
    }

    /**
     * Create a new cashier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed|string|min:6|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => User::ROLE_CASHIER,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->successResponse($user, 'Cashier created successfully', 201);
    }

    /**
     * Update cashier data (only provided fields will be updated)
     */
    public function update(Request $request, User $user)
    {
        // Only cashiers can be modified
        if ($user->role !== User::ROLE_CASHIER) {
            return $this->errorResponse('Cannot modify this user', null, 403);
        }

        $valid = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'sometimes|confirmed|string|min:6|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        // Build update data from only the fields sent in the request
        $data = collect($valid)->only(array_keys($request->all()))->toArray();

        // Hash password if it was provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        // Force logout if password was changed
        if (isset($valid['password'])) {
            $user->tokens()->delete();
        }

        return $this->successResponse($user, 'Cashier updated successfully');
    }

    /**
     * Disable a cashier account
     */
    public function disable(User $user)
    {
        // Only cashiers can be disabled
        if ($user->role !== User::ROLE_CASHIER) {
            return $this->errorResponse('Cannot modify this user', null, 403);
        }

        $user->update(['is_active' => false]);

        // Force logout immediately after disabling
        $user->tokens()->delete();

        return $this->successResponse($user, 'Cashier disabled successfully');
    }

    /**
     * Enable a cashier account
     */
    public function enable(User $user)
    {
        // Only cashiers can be enabled
        if ($user->role !== User::ROLE_CASHIER) {
            return $this->errorResponse('Cannot modify this user', null, 403);
        }

        $user->update(['is_active' => true]);

        return $this->successResponse($user->fresh(), 'Cashier enabled successfully');
    }
}
