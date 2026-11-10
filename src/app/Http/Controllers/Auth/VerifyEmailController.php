<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Refresh the current guard instance when this signed link is opened in an authenticated browser.
        if ($request->user()?->is($user)) {
            $request->user()->refresh();
        }

        if ($user->phone_verification_required && ! $user->hasVerifiedPhone()) {
            return redirect()
                ->route('phone.verification.notice', ['email' => $user->email])
                ->with('status', __('auth.email_verified_still_phone'));
        }

        return redirect()->route('login')->with('status', __('auth.email_verified_login'));
    }
}
