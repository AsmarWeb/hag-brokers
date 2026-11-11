<?php

namespace App\Http\Middleware;

use App\Models\CompanyMember;
use App\Services\Platform\CompanyWorkspaceService;
use Closure;
use Illuminate\Http\Request;

final class EnsureCompanyWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user?->hasVerifiedEmail(), 403, 'Email verification is required.');
        if (! $user->hasVerifiedPhone()) {
            if ($request->expectsJson()) {
                abort(403, 'Phone verification is required.');
            }

            return redirect()->route('phone.verification.notice');
        }

        if (! CompanyMember::where('user_id', $user->id)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Complete company onboarding first.', 'error_code' => 'COMPANY_ONBOARDING_REQUIRED'], 403);
            }

            return redirect()->route('company.onboarding.show');
        }

        app(CompanyWorkspaceService::class)->workspaceFor($user);

        return $next($request);
    }
}
