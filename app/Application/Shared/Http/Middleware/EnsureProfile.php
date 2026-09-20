<?php

namespace App\Application\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->needsProfile()) {
            return $next($request);
        }

        if ($request->routeIs('account.directory.*', 'logout', 'locale.update') || $request->is('logout')) {
            return $next($request);
        }

        return redirect()->route('account.directory.edit');
    }
}
