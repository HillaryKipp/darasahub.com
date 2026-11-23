<?php

use App\Http\Middleware\EnforceCanonicalUrlMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SlugHistoryRedirectMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend([
            EnforceCanonicalUrlMiddleware::class,
            SlugHistoryRedirectMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '_maintenance/*',
        ]);

        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
            \Spatie\ResponseCache\Middlewares\CacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
