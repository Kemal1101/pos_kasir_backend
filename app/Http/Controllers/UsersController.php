<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class UsersController extends Controller
{
    /**
     * Create a new user.
     */
    public function add_user(Request $request)
    {
        try {
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

            return Response::success($user, 'User berhasil dibuat');
        } catch (ValidationException $e) {
            return Response::error($e, 'Validasi gagal');
        } catch (Throwable $e) {
            return Response::error($e, 'Gagal membuat user');
        }
    }

    /**
     * Update an existing user.
     */
    public function edit_user(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (! $user) {
                return Response::notFound("User Not Found");
            }

            if (! $request->hasAny(['name', 'username', 'email', 'password'])) {
                return Response::error(null, 'Minimal ubah salah satu data', 400);
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
            return Response::success($user, "Berhasil Update User");
        } catch (ValidationException $e) {
            return Response::error($e, 'Validasi gagal');
        } catch (Throwable $e) {
            return Response::error($e, 'Gagal membuat user');
        }
    }

    /**
     * Delete a user.
     */
    public function delete_user($id)
    {
        try {
            $user = User::find($id);
            if (! $user) {
                return Response::notFound("User Not Found");
            }

            $user->delete();

            return Response::success(null, "Berhasil Menghapus User");
        } catch (Throwable $e) {
            return Response::error($e, "Terjadi kesalahan saat menghapus user", 500);
        }
    }

    /**
     * List users with their role.
     */
    public function list_user(Request $request)
    {
        try {
            //code...
            $query = User::with('role');

            if ($request->filled('role_id')) {
                $query->where('role_id', $request->input('role_id'));
            }

            $users = $query->get();
            if ($users->isEmpty()) {
                # code...
                return Response::notFound("Tidak ada user ditemukan");
            }
            return Response::success($users, 'Daftar user berhasil diambil');
        } catch (Throwable $error) {
            return Response::error($error, 'Gagal mengambil data user');
        }
    }
}
