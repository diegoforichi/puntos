<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('superadmin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])
            ->active()
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password) || !$user->isSuperAdmin()) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciales inválidas.');
        }

        Auth::guard('superadmin')->login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->route('superadmin.dashboard')->with('success', 'Bienvenido al Panel SuperAdmin.');
    }

    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Sesión finalizada.');
    }
}
