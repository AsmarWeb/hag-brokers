<?php

namespace App\Http\Controllers;

use App\Models\CompanyPlatformAudit;
use App\Models\CompanyPublicProfile;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompanyProfileController extends Controller
{
    public function edit(Request $request, CompanyMarketplaceService $market)
    {
        $w = $market->actor($request->user(), true);
        $profile = CompanyPublicProfile::where('workspace_id', $w->id)->first() ?? new CompanyPublicProfile;

        return response()->view('company.profile.edit', compact('profile'))->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, CompanyMarketplaceService $market)
    {
        $market->actor($request->user(), true);
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:0'],
            'name_ar' => ['nullable', 'string', 'max:160'], 'name_en' => ['nullable', 'string', 'max:160'],
            'about_ar' => ['nullable', 'string', 'max:3000'], 'about_en' => ['nullable', 'string', 'max:3000'],
            'sector' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'specialties' => ['nullable', 'string', 'max:1000'], 'markets' => ['nullable', 'string', 'max:300'],
            'website' => ['nullable', 'url:http,https', 'max:500'],
            'is_published' => ['required', 'boolean'],
            'workspace_id' => ['prohibited'], 'customer_id' => ['prohibited'], 'slug' => ['prohibited'], 'is_verified' => ['prohibited'],
        ]);
        DB::transaction(function () use ($request, $market, $data) {
            $w = $market->actor($request->user(), true);
            CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
            // Recheck membership and active state after taking the workspace lock.
            $market->actor($request->user(), true);
            $profile = CompanyPublicProfile::where('workspace_id', $w->id)->lockForUpdate()->first();
            if ((int) ($profile?->version ?? 0) !== (int) $data['version']) {
                throw ValidationException::withMessages(['version' => __('company_profile.stale')]);
            }
            $profile ??= new CompanyPublicProfile(['workspace_id' => $w->id, 'slug' => 'company-'.Str::uuid()]);
            foreach (['name_ar', 'name_en', 'about_ar', 'about_en', 'sector', 'country', 'city', 'specialties', 'markets', 'website'] as $field) {
                $profile->$field = isset($data[$field]) ? (trim($data[$field]) ?: null) : null;
            }
            $profile->is_published = (bool) $data['is_published'];
            if ($profile->is_published && ! $profile->ready()) {
                throw ValidationException::withMessages(['is_published' => __('company_profile.incomplete')]);
            }
            $profile->version = (int) $data['version'] + 1;
            $profile->save();
            CompanyPlatformAudit::create(['company_workspace_id' => $w->id, 'actor_id' => $request->user()->id, 'action' => 'company.profile_saved', 'details' => ['profile_id' => $profile->id, 'version' => $profile->version, 'published' => $profile->is_published], 'created_at' => now('UTC')]);
        }, 3);

        return redirect()->route('company.profile.edit')->with('success', __('company_profile.saved'));
    }

    public function preview(Request $request, CompanyMarketplaceService $market)
    {
        $w = $market->actor($request->user(), true);
        $profile = CompanyPublicProfile::where('workspace_id', $w->id)->firstOrFail();

        return $this->display($profile, $market, true);
    }

    public function show(string $slug, CompanyMarketplaceService $market)
    {
        $profile = CompanyPublicProfile::where('slug', $slug)->where('is_published', true)
            ->whereIn('workspace_id', CompanyWorkspace::where('status', 'active')->whereIn('customer_id', Customer::where('is_active', true)->select('id'))->select('id'))->firstOrFail();

        return $this->display($profile, $market, false);
    }

    private function display(CompanyPublicProfile $profile, CompanyMarketplaceService $market, bool $preview)
    {
        $listings = $market->publicQuery()->with('media')->where('company_workspace_id', $profile->workspace_id)->latest('id')->paginate(12)->withQueryString();

        return response()->view('company.profile.show', compact('profile', 'listings', 'preview'))->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
