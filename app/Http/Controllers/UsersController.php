<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * Create a new user.
     */
    public function add_user(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'username' => 'required|string|max:191|unique:users,username',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,role_id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'uuid' => (string) Str::uuid(),
            'role_id' => $data['role_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'User created',
            'data' => $user,
        ], 201);
    }

    /**
     * Update an existing user.
     */
    public function edit_user(Request $request, $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'username' => 'sometimes|required|string|max:191|unique:users,username,' . $id . ',user_id',
            'email' => 'sometimes|required|email|max:191|unique:users,email,' . $id . ',user_id',
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,role_id',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Delete a user.
     */
    public function delete_user($id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    /**
     * List users with their role.
     */
    public function list_user(Request $request): JsonResponse
    {
        $query = User::with('role');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        $users = $query->get();

        return response()->json(['data' => $users]);
    }
}
