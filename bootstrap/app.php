<?php

use App\Application\Shared\Http\Middleware\EnsureAdmin;
use App\Application\Shared\Http\Middleware\EnsureProfile;
use App\Application\Shared\Http\Middleware\EnsureStaff;
use App\Application\Shared\Http\Middleware\HandleInertiaRequests;
use App\Application\Shared\Http\Middleware\SetLocale;
use App\Application\Shared\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->alias([
            'staff' => EnsureStaff::class,
            'admin' => EnsureAdmin::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureProfile::class,
            SetSecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($response->getStatusCode() === 404 && ! $request->expectsJson()) {
                return Inertia::render('errors/404')
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            return $response;
        });
    })->create();
