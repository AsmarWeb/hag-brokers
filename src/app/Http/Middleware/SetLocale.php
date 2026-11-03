<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = in_array($request->query('lang'), ['en', 'ar'], true)
            ? $request->query('lang') : session('locale', config('app.locale'));

        if (in_array($request->query('lang'), ['en', 'ar'], true)) {
            session(['locale' => $locale]);
        }

        if (in_array($locale, ['en', 'ar'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
