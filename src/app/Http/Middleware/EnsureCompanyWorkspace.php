<?php

namespace App\Http\Middleware;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Services\Platform\CompanyWorkspaceService;
use Closure;
use Illuminate\Http\Request;

final class EnsureCompanyWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user?->hasVerifiedEmail(), 403, 'Email verification is required.');

        // Platform administrators keep their existing admin-only authorization path.
        if ($user->hasRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        if ($user->phone_verification_required && ! $user->hasVerifiedPhone()) {
            if ($request->expectsJson()) {
                abort(403, 'Phone verification is required.');
            }

            return redirect()->route('phone.verification.notice');
        }

        $member = CompanyMember::where('user_id', $user->id)->first();
        if ($member) {
            app(CompanyWorkspaceService::class)->workspaceFor($user);

            return $next($request);
        }

        // Existing customer accounts pre-date workspaces. Keep their customer-scoped
        // shipment and quotation routes working without silently creating a workspace.
        if ($user->customer_id !== null) {
            abort_unless(Customer::whereKey($user->customer_id)->where('is_active', true)->exists(), 403);

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Complete company onboarding first.', 'error_code' => 'COMPANY_ONBOARDING_REQUIRED'], 403);
        }

        return redirect()->route('company.onboarding.show');
    }
}
