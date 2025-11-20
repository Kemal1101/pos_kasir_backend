<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', 'Email atau password salah');
        }

        $request->session()->put('auth_user_id', $user->user_id);
        $request->session()->put('auth_user_role', $user->role_id);

        return redirect()->intended(route('dashboard')); // intended fallback
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['auth_user_id','auth_user_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.form')->with('success', 'Berhasil logout');
    }
}
