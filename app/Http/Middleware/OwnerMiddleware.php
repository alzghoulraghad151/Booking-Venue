<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
       $user = $request->user();

abort_unless(
    $user?->role === 'venue_owner' && $user->status === 'active',
    403
);

return $next($request);
    }
}