<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const OWNER_ONLY = ['owner'];

    // ── index ──────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        return Inertia::render('Accounts', [
            'users' => User::all(),
        ]);
    }

    // ── store ──────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'pin'       => 'required|string|min:4|max:20',
            'role'      => 'required|in:owner,admin,manager,service_advisor,dispatcher,technician,receptionist',
            'sections'  => 'sometimes|array',
            'sections.*'=> 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['pin_hash'] = Hash::make($data['pin']);
        unset($data['pin']);
        $data['is_active'] ??= true;

        $user = User::create($data);

        ActivityLogger::log(
            'Tạo tài khoản',
            "Tạo tài khoản '{$user->username}' ({$user->role})",
            $request->user()->name,
            'user',
            $user->id,
        );

        return back()->with('success', "Đã tạo tài khoản {$user->username}");
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'role'      => 'sometimes|in:owner,admin,manager,service_advisor,dispatcher,technician,receptionist',
            'sections'  => 'sometimes|array',
            'sections.*'=> 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($data);

        ActivityLogger::log(
            'Cập nhật tài khoản',
            "Cập nhật tài khoản '{$user->username}'",
            $request->user()->name,
            'user',
            $user->id,
        );

        return back()->with('success', "Đã cập nhật tài khoản {$user->username}");
    }

    // ── resetPin ───────────────────────────────────────────────────────────

    public function resetPin(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $data = $request->validate([
            'pin' => 'required|string|min:4|max:20',
        ]);

        $user->update(['pin_hash' => Hash::make($data['pin'])]);

        ActivityLogger::log(
            'Đặt lại PIN',
            "Đặt lại PIN cho '{$user->username}'",
            $request->user()->name,
            'user',
            $user->id,
        );

        return back()->with('success', "Đã đặt lại PIN cho {$user->username}");
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, self::OWNER_ONLY), 403);

        $username = $user->username;
        $userId   = $user->id;

        $user->delete();

        ActivityLogger::log(
            'Xóa tài khoản',
            "Đã xóa tài khoản '{$username}'",
            $request->user()->name,
            'user',
            $userId,
        );

        return back()->with('success', "Đã xóa tài khoản {$username}");
    }
}