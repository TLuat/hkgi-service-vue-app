<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUser
{
    /** Routes that do not require authentication. */
    private const GUEST_PATHS = ['login'];

    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('auth_user_id');
        $user   = $userId ? User::find($userId) : null;

        if ($user && $user->is_active) {
            $request->setUserResolver(fn () => $user);
        } else {
            if ($userId) {
                // Stale session — clean up
                session()->forget('auth_user_id');
            }

            if (! $this->isGuestPath($request)) {
                return $this->unauthenticated($request);
            }
        }

        return $next($request);
    }

    private function isGuestPath(Request $request): bool
    {
        return in_array($request->path(), self::GUEST_PATHS, strict: true);
    }

    private function unauthenticated(Request $request): Response
    {
        if ($request->header('X-Inertia')) {
            return response('', 409)
                ->header('X-Inertia-Location', url('/login'));
        }

        return redirect('/login');
    }
}