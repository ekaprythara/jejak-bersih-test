<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException; // 1. Impor kelas ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            "isOwner" => OwnerMiddleware::class,
            "isAdmin" => AdminMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 2. Tangkap AccessDeniedHttpException (Error HTTP 403 asli)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {

            // Jika request berupa API/AJAX murni yang mengharapkan JSON
            if ($request->wantsJson() && ! $request->hasHeader('X-Inertia')) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            // Jika dipanggil via Inertia / Web Browser biasa
            return redirect()
                ->route('dashboard')
                ->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        });
    })->create();
