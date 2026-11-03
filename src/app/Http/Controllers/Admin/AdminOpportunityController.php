<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use App\Services\Platform\OpportunityModerationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminOpportunityController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $market): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'], 'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'opportunity_type' => ['nullable', Rule::in(OpportunityCatalog::TYPES)], 'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'subcategory' => ['nullable', Rule::in(OpportunityCatalog::SUBCATEGORIES[$request->input('category')] ?? [])],
            'review' => ['nullable', 'in:hidden,clear'], 'publication' => ['nullable', 'in:public,unavailable'],
            'verification_status' => ['nullable', 'in:pending,under_review,verified,rejected,auto_eligible'],
            'visibility' => ['nullable', 'in:public,private,invite_only'], 'stage' => ['nullable', 'in:new,qualified,quotation,negotiation,won,lost'],
            'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
        ]);
        $query = Opportunity::with(['customer', 'vessel', 'moderation'])->withCount('interests');
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')->orWhere('slug', 'like', '%'.$search.'%')->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', '%'.$search.'%'));
            });
        }
        foreach (['customer_id', 'opportunity_type', 'category', 'verification_status', 'visibility', 'stage'] as $field) {
            if ($filters[$field] ?? null) {
                $query->where($field, $filters[$field]);
            }
        }
        if ($filters['subcategory'] ?? null) {
            $query->where('commercial_details->subcategory', $filters['subcategory']);
        }
        if (($filters['review'] ?? '') === 'hidden') {
            $query->whereHas('moderation', fn ($q) => $q->where('hidden', true));
        }
        if (($filters['review'] ?? '') === 'clear') {
            $query->whereDoesntHave('moderation', fn ($q) => $q->where('hidden', true));
        }
        $publicIds = fn () => $market->publicQuery()->select('opportunities.id');
        if (($filters['publication'] ?? '') === 'public') {
            $query->whereIn('id', $publicIds());
        }
        if (($filters['publication'] ?? '') === 'unavailable') {
            $query->whereNotIn('id', $publicIds());
        }
        if ($filters['from'] ?? null) {
            $query->where('created_at', '>=', CarbonImmutable::parse($filters['from'], 'Asia/Dubai')->startOfDay()->utc());
        }
        if ($filters['to'] ?? null) {
            $query->where('created_at', '<', CarbonImmutable::parse($filters['to'], 'Asia/Dubai')->addDay()->startOfDay()->utc());
        }
        $stats = ['total' => Opportunity::count(), 'public' => $market->publicQuery()->count(), 'hidden' => Opportunity::whereHas('moderation', fn ($q) => $q->where('hidden', true))->count(), 'interests' => OpportunityInterest::count()];
        $opportunities = $query->latest('id')->paginate(20)->withQueryString();
        $publicOnPage = $market->publicQuery()->whereIn('id', $opportunities->modelKeys())->pluck('id')->all();
        $customers = Customer::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.opportunities.index', compact('opportunities', 'customers', 'stats', 'publicOnPage'));
    }

    public function show(Opportunity $opportunity, CompanyMarketplaceService $market): View
    {
        $opportunity->load(['customer', 'contact', 'assignedTo', 'originPort', 'destinationPort', 'vessel', 'media', 'moderation']);
        $interests = $opportunity->interests()->with(['user.customer', 'reviewer'])->latest('id')->paginate(10, ['*'], 'interests_page')->withQueryString();
        $events = $opportunity->moderationEvents()->with('actor')->latest('version')->paginate(10, ['*'], 'history_page')->withQueryString();
        $public = $market->isPublic($opportunity);
        $submitter = User::find($opportunity->submitted_by);
        $workspace = CompanyWorkspace::find($opportunity->company_workspace_id);
        $blockers = [];
        if ($opportunity->moderation?->hidden) {
            $blockers[] = 'hidden';
        }
        if ($opportunity->visibility !== 'public') {
            $blockers[] = 'visibility';
        }
        if (! in_array($opportunity->verification_status, ['verified', 'auto_eligible'], true)) {
            $blockers[] = 'eligibility';
        }
        if (! $opportunity->published_at || $opportunity->published_at->isFuture()) {
            $blockers[] = 'publication_date';
        }
        if ($opportunity->expires_at && ! $opportunity->expires_at->isFuture()) {
            $blockers[] = 'expiry';
        }
        if ($opportunity->marketplace_state !== null && $opportunity->marketplace_state !== 'published') {
            $blockers[] = 'closed';
        }
        if ($opportunity->company_workspace_id && (! $workspace || $workspace->status !== 'active' || ! Customer::whereKey($workspace->customer_id)->where('is_active', true)->exists())) {
            $blockers[] = 'company';
        }

        return view('admin.opportunities.show', compact('opportunity', 'interests', 'events', 'public', 'submitter', 'workspace', 'blockers'));
    }

    public function moderate(Request $request, Opportunity $opportunity, OpportunityModerationService $service)
    {
        $data = $request->validate(['action' => ['required', 'in:hide,restore,note'], 'reason' => ['required', 'string', 'min:10', 'max:2000'], 'version' => ['required', 'integer', 'min:0'], 'confirmed' => ['required', 'accepted']]);
        $service->apply($request->user(),$opportunity,$data);

        return redirect()->route('admin.opportunities.show',$opportunity)->with('success',__('admin_opportunities.saved'));
    }
}
