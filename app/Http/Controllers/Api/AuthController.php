<?php

namespace App\Http\Controllers\Api;

use App\Utils\Response;
use Illuminate\Http\Request;
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
}
