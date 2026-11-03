<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MobileApi
{
    public function handle(Request $request, Closure $next)
    {
        // API clients always receive JSON, including framework errors.
        $request->headers->set('Accept', 'application/json');
        $lang = $request->query('lang');
        if ($lang !== null && ! in_array($lang, ['ar', 'en'], true)) {
            throw ValidationException::withMessages(['lang' => ['Use ar or en.']]);
        }
        app()->setLocale($lang ?? (str_starts_with(strtolower((string) $request->header('Accept-Language')), 'ar') ? 'ar' : 'en'));
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('Content-Language', app()->getLocale());
        $response->headers->set('X-API-Version', '1');

        return $response;
    }
}
