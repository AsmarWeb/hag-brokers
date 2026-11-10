<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->hasVerifiedAccount()) {
            if ($request->expectsJson()) {
                abort(403, __('market.email_required'));
            }

            return redirect()->guest(route('verification.notice'));
        }

        return $next($request);
    }
}
