<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        if (! $request->user()->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda dinonaktifkan.']);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectByRole($request->user()));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectByRole(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Kasir => route('pos.index'),
            UserRole::Dapur => route('kitchen.index'),
            default => '/',
        };
    }
}
