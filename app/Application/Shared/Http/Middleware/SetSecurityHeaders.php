<?php

namespace App\Application\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $devServer = app()->isLocal() ? ' http://localhost:5173 ws://localhost:5173' : '';

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'".$devServer,
            "style-src 'self' 'unsafe-inline'".$devServer,
            "img-src 'self' data: blob: https:",
            "font-src 'self' data:",
            "connect-src 'self'".$devServer,
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
        ];

        return implode('; ', $directives);
    }
}
