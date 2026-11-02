<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Http\Request;

class AdminCompanyWorkspaceController extends Controller
{
    public function index()
    {
        return view('admin.platform.index', [
            'workspaces' => CompanyWorkspace::orderByDesc('id')->paginate(20),
            'customers' => Customer::where('is_active', true)->orderBy('company_name')->get(['id', 'company_name']),
            'users' => User::whereNotNull('customer_id')->orderBy('name')->get(['id', 'name', 'email', 'customer_id']),
            'plans' => PlatformPlan::orderBy('monthly_amount_minor')->get(),
        ]);
    }

    public function store(Request $request, CompanyWorkspaceService $service)
    {
        $data = $request->validate(['customer_id' => ['required', 'integer', 'exists:customers,id'], 'owner_id' => ['required', 'integer', 'exists:users,id']]);
        $w = $service->activate(Customer::findOrFail($data['customer_id']), User::findOrFail($data['owner_id']), $request->user());

        return redirect()->route('admin.platform.show', $w)->with('success', __('platform.saved'));
    }

    public function show(CompanyWorkspace $workspace, CompanyWorkspaceService $service)
    {
        return view('admin.platform.show', [
            'workspace' => $workspace, 'company' => Customer::findOrFail($workspace->customer_id),
            'plan' => $service->plan($workspace), 'plans' => PlatformPlan::where('is_available', true)->where('code', '!=', 'free')->get(),
            'members' => CompanyMember::where('company_workspace_id', $workspace->id)->get(),
            'users' => User::where('customer_id', $workspace->customer_id)->get(),
            'audits' => CompanyPlatformAudit::where('company_workspace_id', $workspace->id)->latest('id')->paginate(15),
        ]);
    }

    public function member(Request $request, CompanyWorkspace $workspace, CompanyWorkspaceService $service)
    {
        $d = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id'], 'role' => ['required', 'in:manager,member']]);
        $service->addMember($workspace, User::findOrFail($d['user_id']), $d['role'], $request->user());

        return back()->with('success', __('platform.saved'));
    }

    public function remove(Request $request, CompanyWorkspace $workspace, CompanyMember $member, CompanyWorkspaceService $service)
    {
        $service->removeMember($workspace, $member, $request->user());

        return back()->with('success', __('platform.saved'));
    }

    public function trial(Request $request, CompanyWorkspace $workspace, CompanyWorkspaceService $service)
    {
        $d = $request->validate(['plan_id' => ['required', 'integer', 'exists:platform_plans,id'], 'days' => ['required', 'integer', 'in:7,14,30'], 'reason' => ['required', 'string', 'max:1000']]);
        $service->trial($workspace, PlatformPlan::findOrFail($d['plan_id']), $d['days'], $d['reason'], $request->user());

        return back()->with('success', __('platform.saved'));
    }

    public function status(Request $request, CompanyWorkspace $workspace, CompanyWorkspaceService $service)
    {
        $d = $request->validate(['status' => ['required', 'in:active,suspended'], 'reason' => ['required', 'string', 'max:1000']]);
        $service->setStatus($workspace, $d['status'], $d['reason'], $request->user());

        return back()->with('success',__('platform.saved'));
    }
}
