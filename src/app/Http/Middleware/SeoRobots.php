<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SeoRobots
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $route = $request->route()?->getName() ?? '';
        $public = in_array($route, ['home', 'opportunities.index', 'opportunities.show'], true);
        $discovery = str_starts_with($route, 'seo.');
        $filtered = $route === 'opportunities.index' && collect(['search', 'type', 'category', 'country', 'subcategory', 'vessel_category', 'currency', 'min_price', 'max_price', 'sort'])->contains(fn ($key) => $request->filled($key));
        if (! config('seo.indexing_enabled') || (! $public && ! $discovery) || $filtered) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
