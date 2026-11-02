<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Http\Request;

class CompanyWorkspaceController extends Controller
{
    public function show(Request $request, CompanyWorkspaceService $service)
    {
        $w = $service->workspaceFor($request->user());

        return view('company.workspace', [
            'workspace' => $w, 'company' => Customer::findOrFail($w->customer_id), 'plan' => $service->plan($w),
            'members' => CompanyMember::where('company_workspace_id', $w->id)->get()->map(fn ($m) => ['name' => User::find($m->user_id)?->name, 'role' => $m->role]),
        ]);
    }
}
