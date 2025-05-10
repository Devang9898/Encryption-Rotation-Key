<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Passport; // <-- Import Passport

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            //
            // Passport::routes(); // <-- Register Passport's internal routes
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ... your global middleware configuration ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ... your exception handling configuration ...
    })->create();