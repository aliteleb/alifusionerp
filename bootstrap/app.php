<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS middleware must be at the very beginning
        $middleware->prepend(HandleCors::class);

        // Validate CSRF tokens for all routes except the ones specified in the except array
        // $middleware->validateCsrfTokens(except: [
        //     '*', // Exclude all routes
        // ]);

        $middleware->alias([
            'localization' => \App\Http\Middleware\LocalizationMiddleware::class,
            'track.activity' => \Modules\Core\Http\Middleware\TrackUserActivity::class,
            'tenant' => \Modules\Core\Http\Middleware\TenantDatabaseMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
