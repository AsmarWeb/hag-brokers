<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Http\Request;

class CompanyWorkspaceController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);
        if (! $request->user()->hasVerifiedPhone()) {
            return redirect()->route('phone.verification.notice');
        }

        abort_if(CompanyMember::where('user_id', $request->user()->id)->exists(), 409);

        return view('company.onboarding');
    }

    public function onboarding(Request $request)
    {
        $user = $request->user();
        $member = CompanyMember::where('user_id', $user->id)->first();

        return response()->json(['data' => [
            'required' => ! $member,
            'email_verified' => $user->hasVerifiedEmail(),
            'phone' => $user->phone,
            'phone_verified' => $user->hasVerifiedPhone(),
            'company' => $member ? ['workspace_id' => $member->company_workspace_id, 'role' => $member->role] : null,
        ]]);
    }

    public function store(Request $request, CompanyWorkspaceService $service)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'regex:/^[A-Za-z]{2}$/D'],
            'city' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'role' => ['prohibited'],
        ]);

        $workspace = $service->createForOwner($request->user(), $data);

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'workspace_id' => $workspace->id,
                'status' => $workspace->status,
                'role' => 'owner',
            ]], 201);
        }

        return redirect()->route('company.workspace')->with('status', __('platform.workspace_activated'));
    }

    public function show(Request $request, CompanyWorkspaceService $service)
    {
        $w = $service->workspaceFor($request->user());

        return view('company.workspace', [
            'workspace' => $w, 'company' => Customer::findOrFail($w->customer_id), 'plan' => $service->plan($w),
            'members' => CompanyMember::where('company_workspace_id', $w->id)->get()->map(fn ($m) => ['name' => User::find($m->user_id)?->name, 'role' => $m->role]),
        ]);
    }
}
