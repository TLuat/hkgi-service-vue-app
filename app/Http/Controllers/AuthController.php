<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response|RedirectResponse
    {
        if (session('auth_user_id')) {
            return redirect('/');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'pin'      => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                    ->where('is_active', true)
                    ->first();

        if (! $user || ! $user->checkPin($request->pin)) {
            return back()->withErrors([
                'pin' => 'Tên đăng nhập hoặc PIN không đúng',
            ]);
        }

        $request->session()->regenerate();
        session()->put('auth_user_id', $user->id);

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget('auth_user_id');
        $request->session()->regenerate();

        return redirect('/login');
    }

    public function changePin(Request $request): RedirectResponse
    {
        $request->validate([
            'currentPin' => 'required|string',
            'newPin'     => 'required|string|min:4',
        ]);

        $user = $request->user();

        if (! $user->checkPin($request->currentPin)) {
            return back()->withErrors([
                'currentPin' => 'PIN hiện tại không đúng',
            ]);
        }

        $user->update(['pin_hash' => Hash::make($request->newPin)]);

        return back()->with('success', 'Đổi PIN thành công');
    }
}