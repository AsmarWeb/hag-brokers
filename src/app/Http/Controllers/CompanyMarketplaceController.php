<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyMarketplaceController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $service)
    {
        $w = $service->actor($request->user());
        $canPublish = CompanyMember::where('company_workspace_id', $w->id)->where('user_id', $request->user()->id)->whereIn('role', ['owner', 'manager'])->exists();
        $filters = $request->validate(['listing_status' => ['nullable', 'in:visible,hidden,closed,expired,unavailable']]);
        $own = fn () => Opportunity::where('company_workspace_id', $w->id);
        $public = fn () => $service->publicQuery()->where('company_workspace_id', $w->id)->select('opportunities.id');
        $stats = ['total' => $own()->count(), 'visible' => $service->publicQuery()->where('company_workspace_id', $w->id)->count(), 'hidden' => $own()->whereHas('moderation', fn ($q) => $q->where('hidden', true))->count(), 'pending' => $canPublish ? OpportunityInterest::where('status', 'pending')->whereIn('opportunity_id', $own()->select('id'))->count() : null];
        $query = $own()->with(['moderation', 'media', 'vessel'])->withCount('interests');
        match ($filters['listing_status'] ?? '') {
            'visible' => $query->whereIn('id', $public()),
            'hidden' => $query->whereHas('moderation', fn ($q) => $q->where('hidden', true)),
            'closed' => $query->where('marketplace_state', 'closed'),
            'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()),
            'unavailable' => $query->whereNotIn('id', $public()),
            default => null
        };
        $opportunities = $query->latest('id')->paginate(10, ['*'], 'opportunities_page')->withQueryString();
        $visibleIds = $service->publicQuery()->whereIn('id', $opportunities->modelKeys())->pluck('id')->all();
        $outgoing = OpportunityInterest::with(['opportunity'])->where('company_workspace_id', $w->id)->latest('id')->paginate(10, ['*'], 'outgoing_page')->withQueryString();
        $incoming = $canPublish ? OpportunityInterest::with(['opportunity', 'user'])->whereIn('opportunity_id', $own()->select('id'))->latest('id')->paginate(10, ['*'], 'incoming_page')->withQueryString() : null;
        $applicantIds = $incoming ? $incoming->pluck('company_workspace_id')->filter()->unique() : collect();
        $applicantCompanies = DB::table('company_workspaces')->join('customers', 'customers.id', '=', 'company_workspaces.customer_id')->whereIn('company_workspaces.id', $applicantIds)->pluck('customers.company_name', 'company_workspaces.id');

        return view('company.marketplace', compact('w', 'canPublish', 'opportunities', 'outgoing', 'incoming', 'stats', 'visibleIds', 'applicantCompanies') + ['usage' => $service->usage($w)]);
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
