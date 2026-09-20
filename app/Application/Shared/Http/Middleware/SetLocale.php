<?php

namespace App\Application\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('localization.available');
        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! in_array($locale, $available, true)) {
            $locale = $request->user()->locale
                ?? $request->getPreferredLanguage($available)
                ?? config('app.locale');
        }

        if (! in_array($locale, $available, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
