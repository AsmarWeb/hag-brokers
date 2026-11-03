<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\SeoRobots;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            SeoRobots::class,
        ]);

        $middleware->alias([
            'maintenance.check' => CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response) {
            if (request()->is('api/v1', 'api/v1/*')) {
                $response->headers->set('Cache-Control', 'private, no-store');
                $response->headers->set('X-API-Version', '1');
            }

            return $response;
        });
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => $request->is('api/v1', 'api/v1/*') || $request->expectsJson());
    })->create();
