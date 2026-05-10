<?php

use App\Http\Middleware\CheckIsBlocked;
use App\Http\Middleware\CheckPremiumSubscription;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();

        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'premium' => CheckPremiumSubscription::class,
            'role' => CheckRole::class,
            'is_blocked' => CheckIsBlocked::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/auth/register',
            'api/auth/login',
            'api/stripe/webhook',
            '*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
