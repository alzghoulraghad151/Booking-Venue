<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }
        $user = $request->user();

        abort_unless($user?->role === 'admin' && $user->status === 'active', 403);

        return $next($request);
    }
}
