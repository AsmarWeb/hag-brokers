<?php

namespace App\Http\Controllers;

use App\Models\CompanyWorkspaceClosureRequest;
use App\Services\Platform\CompanyWorkspaceClosureService;
use Illuminate\Http\Request;

final class CompanyWorkspaceClosureController extends Controller
{
    public function start(Request $request, CompanyWorkspaceClosureService $service)
    {
        $service->start($request->user());
        return redirect()->route('company.workspace.closure.status')->with('status', 'A verification email has been sent to the company owner email address.');
    }

    public function status(Request $request)
    {
        $closure = CompanyWorkspaceClosureRequest::where('owner_id', $request->user()->id)->latest('id')->firstOrFail();
        return view('company.workspace-closure-status', compact('closure'));
    }

    public function showVerification(string $token, CompanyWorkspaceClosureService $service)
    {
        abort_unless($service->tokenIsValid($token), 404);
        return view('company.workspace-closure-verify', compact('token'));
    }

    public function verify(Request $request, string $token, CompanyWorkspaceClosureService $service)
    {
        $closure = $service->verifyEmail($token);
        abort_unless($closure, 410);

        if (auth()->check() && auth()->id() !== (int) $closure->owner_id) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'status',
                'Email verified. Sign in using the company owner account to continue.'
            );
        }

        if (auth()->check()) {
            return redirect()->route('company.workspace.closure.status')
                ->with('status', 'Email verified. Review and explicitly confirm the closure.');
        }

        return redirect()->route('login')
            ->with('status', 'Email verified. Sign in as the company owner to explicitly confirm closure.');
    }

    public function confirm(Request $request, CompanyWorkspaceClosureService $service)
    {
        $request->validate(['confirm_understood' => ['required', 'accepted']]);
        $closure = $service->confirmByOwner($request->user());
        return redirect()->route('company.workspace.closure.status')->with('status', 'Workspace suspended and sent to platform administration for review.');
    }
}
