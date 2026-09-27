<?php

namespace App\Application\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages answer with markdown when asked for `text/markdown`. Most agents do
 * not send that header, so `/events/foo.md` (and `/index.md` for the home
 * page) is served as the markdown variant of `/events/foo`.
 */
class ServeMarkdownVariant
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if ($request->isMethodSafe() && str_ends_with($path, '.md')) {
            $target = $path === '/index.md' ? '/' : substr($path, 0, -3);
            $query = $request->getQueryString();

            $request = $request->duplicate(server: [
                ...$request->server->all(),
                'REQUEST_URI' => $target.($query === null ? '' : "?{$query}"),
            ]);
            $request->headers->set('Accept', 'text/markdown');
        }

        $response = $next($request);

        // The same URL answers with html or markdown depending on Accept, so
        // caches in front of the site must key on it.
        $response->setVary('Accept', false);

        return $response;
    }
}
