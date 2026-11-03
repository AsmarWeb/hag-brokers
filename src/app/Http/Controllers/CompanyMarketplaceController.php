<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyMarketplaceController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $service)
    {
        $w = $service->actor($request->user());
        $canPublish = CompanyMember::where('company_workspace_id', $w->id)->where('user_id', $request->user()->id)->whereIn('role', ['owner', 'manager'])->exists();
        $opportunities = Opportunity::where('company_workspace_id', $w->id)->latest('id')->paginate(10, ['*'], 'opportunities_page');
        $outgoing = OpportunityInterest::with(['opportunity'])->where('company_workspace_id', $w->id)->latest('id')->paginate(10, ['*'], 'outgoing_page');
        $incoming = $canPublish ? OpportunityInterest::with(['opportunity', 'user'])->whereIn('opportunity_id', Opportunity::where('company_workspace_id', $w->id)->select('id'))->latest('id')->paginate(10, ['*'], 'incoming_page') : null;

        return view('company.marketplace', compact('w', 'canPublish', 'opportunities', 'outgoing', 'incoming') + ['usage' => $service->usage($w)]);
    }

    public function edit(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        $w = $service->actor($request->user(), true);
        abort_unless((int) $opportunity->company_workspace_id === (int) $w->id, 404);

        return view('company.opportunity-edit', compact('opportunity'));
    }

    public function update(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        $w = $service->actor($request->user(), true);
        abort_unless((int) $opportunity->company_workspace_id === (int) $w->id, 404);
        $marine = OpportunityCatalog::isMarine($opportunity->opportunity_type, $request->input('category'), (array) $request->input('commercial_details', []));
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'min:20', 'max:20000'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', Rule::in(array_merge(OpportunityCatalog::CATEGORIES, [$opportunity->category]))]] + OpportunityCatalog::detailRules($request->input('category')) + OpportunityCatalog::vesselRules($marine, $request->input('commercial_details.subcategory')));
        if (! $marine) {
            unset($data['vessel']);
        }
        $service->update($request->user(), $opportunity, $data);

        return redirect()->route('company.marketplace')->with('success', __('market.saved'));
    }

    public function close(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        $service->close($request->user(), $opportunity);

        return back()->with('success', __('market.closed_success'));
    }

    public function decide(Request $request, OpportunityInterest $interest, CompanyMarketplaceService $service)
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        $service->decide($request->user(), $interest, $data['status']);

        return back()->with('success', __('market.saved'));
    }
}
