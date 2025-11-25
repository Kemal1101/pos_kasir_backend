<?php

namespace App\Http\Controllers;

use App\Utils\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (! $token = JWTAuth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email atau password salah'], 401);
            }
            return back()->withInput()->with('error', 'Email atau password salah');
        }

        $user = JWTAuth::user();
        $ttlSeconds = JWTAuth::factory()->getTTL() * 60; // configured minutes * 60

        // Store minimal user info in session for existing web_auth middleware compatibility
        Session::put('auth_user_id', $user->user_id);
        Session::put('auth_role_id', $user->role_id);

        if ($request->expectsJson()) {
            return Response::token($token, $user, $ttlSeconds);
        }

        // Set JWT token in a non-HttpOnly cookie (consider HttpOnly for security if middleware parses it)
        $ttlMinutes = $ttlSeconds / 60;
        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil')
            ->cookie(cookie('jwt_token', $token, $ttlMinutes));
    }

    public function me()
    {
        return Response::success(JWTAuth::user());
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Throwable $e) {
            // ignore invalid token on logout
        }

        Session::forget(['auth_user_id', 'auth_role_id']);

        if (request()->expectsJson()) {
            return Response::success(null, 'Logout berhasil');
        }
        return redirect()->route('login.form')
            ->with('success', 'Logout berhasil')
            ->cookie(Cookie::forget('jwt_token'));
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
    /**
     * Show login form (web).
     */
    public function show()
    {
        return view('login');
    }
}
