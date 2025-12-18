<?php

namespace App\Http\Controllers\Api;

use App\Utils\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        return Response::token(
            $token,
            JWTAuth::user(),
            JWTAuth::factory()->getTTL() * 60
        );
    }

    public function me()
    {
        return Response::success(JWTAuth::user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return Response::success(null, 'Logout berhasil');
    }

    public function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return Response::token(
            $newToken,
            JWTAuth::user(),
            JWTAuth::factory()->getTTL() * 60
        );
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return Response::unauthorized('User not authenticated');
            }

            // Verify old password
            if (!Hash::check($validated['old_password'], $user->password)) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'message' => 'Old password is incorrect',
                    ],
                ], 422);
            }

            // Update password
            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return Response::success(null, 'Password changed successfully');
        } catch (\Throwable $e) {
            return Response::error($e, 'Failed to change password');
        }
    }
}
