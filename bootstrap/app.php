<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RecordAuditTrail;
use App\Http\Middleware\ShareSiteContext;
use App\Support\SeasonContext;
use App\Support\SiteSettings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.access' => EnsureAdminAccess::class,
            'role' => EnsureUserHasRole::class,
            'audit' => RecordAuditTrail::class,
        ]);

        $middleware->web(append: [
            ShareSiteContext::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : null);

        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.dashboard')
            : '/');
    })
    ->withScopedSingletons([
        SeasonContext::class,
        SiteSettings::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
