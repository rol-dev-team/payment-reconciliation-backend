<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users with their roles.
     */
    public function index(Request $request)
    {
        // Optional: pagination, default 10 per page
        $perPage = $request->query('per_page', 10);
        $users = User::with('role')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users',
            'email'     => 'required|email|max:255|unique:users',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role_id'   => $validated['role_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('role')
        ], 201);
    }

    /**
     * Show a single user by ID.
     */
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update an existing user by ID.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'username'  => ['sometimes','required','string','max:255', Rule::unique('users')->ignore($user->id)],
            'email'     => ['sometimes','required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => 'sometimes|nullable|string|min:6',
            'role_id'   => 'sometimes|required|exists:roles,id',
        ]);

        if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('role')
        ]);
    }

    /**
     * Delete a user by ID.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}