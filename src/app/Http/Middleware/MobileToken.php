<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class MobileToken
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        $token = $u?->currentAccessToken();
        abort_unless($request->bearerToken() && $token instanceof PersonalAccessToken, 401);
        abort_unless($token->can('mobile:v1') && ! $u->hasRole(['admin', 'super-admin']), 403);
        abort_unless($u->hasVerifiedEmail(), 403, 'Email verification is required.');

        return $next($request);
    }
}
