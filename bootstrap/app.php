<?php

use App\Http\Middleware\{ 
    HandleAppearance, HandleInertiaRequests, 
    PreventMixedAuthentication, 
    HandleUserRole, HandleSuperAdminRole, 
    CheckPermission, PreventBackHistory};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance']);
        
        $middleware->alias([
            'prevent.mixed.auth' => PreventMixedAuthentication::class,
            'admin' => HandleUserRole::class,
            'superadmin' => HandleSuperAdminRole::class,
            'permission' => CheckPermission::class,
            'prevent-back-history' => PreventBackHistory::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // 1. Where to send NOT logged in users (Voters trying to access protected routes)
        $middleware->redirectGuestsTo(fn (Request $request) => route('home'));

        // 2. Where to send ALREADY logged in users (Admins trying to access /login)
        $middleware->redirectUsersTo(fn (Request $request) => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();