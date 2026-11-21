<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\OrganizerMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'owner' => OwnerMiddleware::class,
            'organizer' => OrganizerMiddleware::class,
        ]);

        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            AdminMiddleware::class,
        );

        $middleware->redirectGuestsTo(function (): string {
            abort_unless(Route::has('login'), 401);

            return route('login');
        });

    $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request): string {
    return match ($request->user()?->role) {
        'venue_owner' => route('owner.venues.index'),
        'admin' => route('admin.dashboard'),
        default => route('home'),
    };
});
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
