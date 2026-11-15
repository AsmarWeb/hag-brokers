<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyWorkspaceClosureRequest;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\CompanyWorkspaceClosureService;
use Illuminate\Http\Request;

final class AdminCompanyWorkspaceClosureController extends Controller
{
    public function approve(CompanyWorkspaceClosureRequest $closure, Request $request, CompanyWorkspaceClosureService $service, CompanyBillingService $billing)
    {
        $service->approve($closure, $request->user(), $billing);
        return back()->with('success', 'Workspace closure approved and completed. Member accounts were detached; historical records were retained.');
    }

    public function reject(CompanyWorkspaceClosureRequest $closure, Request $request, CompanyWorkspaceClosureService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $service->reject($closure, $request->user(), $data['reason']);
        return back()->with('success', 'Workspace closure request rejected.');
    }
}
