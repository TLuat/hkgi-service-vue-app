<?php

use App\Http\Middleware\AuthenticateUser;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // AuthenticateUser must run before HandleInertiaRequests so that
        // $request->user() is populated when Inertia shares auth.user.
        $middleware->web(append: [
            AuthenticateUser::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'auth.user' => AuthenticateUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Let Inertia middleware handle validation errors (redirects back with session errors)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            if (! $request->header('X-Inertia') && ! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpException) {
                // For GET (page navigation): redirect home instead of returning JSON
                if ($request->isMethod('GET')) {
                    $status = $e->getStatusCode();
                    $msg = match($status) {
                        403 => 'Bạn không có quyền truy cập trang này.',
                        404 => 'Trang không tồn tại.',
                        default => $e->getMessage() ?: 'Có lỗi xảy ra.',
                    };
                    return redirect('/')->with('error', $msg);
                }

                return response()->json([
                    'message' => $e->getMessage() ?: 'HTTP error occurred.',
                ], $e->getStatusCode());
            }

            return response()->json([
                'message' => app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'An unexpected error occurred.',
            ], 500);
        });
    })->create();