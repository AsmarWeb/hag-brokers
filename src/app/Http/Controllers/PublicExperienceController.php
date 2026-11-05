<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\PublicExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicExperienceController extends Controller
{
    private function admin(Request $r): void
    {
        abort_unless($r->user()?->hasVerifiedEmail() && $r->user()->hasRole(['admin', 'super-admin']), 403);
    }

    public function download(PublicExperience $service)
    {
        $release = $service->download();
        abort_unless($release, 404);

        return response()->view('public-experience.download', compact('release'))->header('Cache-Control', 'no-store');
    }

    public function map(PublicExperience $service)
    {
        return response()->json(['data' => $service->map()])->header('Cache-Control', 'no-store');
    }

    public function index(Request $r, PublicExperience $service)
    {
        $this->admin($r);

        return response()->view('admin.public-experience.index', ['settings' => $service->settings(),
            'opportunities' => $service->marineQuery()->latest('id')->paginate(20)])->header('Cache-Control', 'no-store');
    }

    public function update(Request $r, PublicExperience $service)
    {
        $this->admin($r);
        $data = $r->validate(['version' => 'required|integer|min:0', 'download_enabled' => 'required|boolean',
            'map_enabled' => 'required|boolean', 'android_url' => 'nullable|string|max:500',
            'android_version' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9._+-]+$/D'],
            'released_on' => 'nullable|date_format:Y-m-d|before_or_equal:today']);
        if (($data['android_url'] ?? null) && ! $service->safeDownloadUrl($data['android_url'])) {
            throw ValidationException::withMessages(['android_url' => __('public_experience.invalid_url')]);
        }
        if ($r->boolean('download_enabled') && (empty($data['android_url']) || empty($data['android_version']) || empty($data['released_on']))) {
            throw ValidationException::withMessages(['android_url' => __('public_experience.release_required')]);
        }
        DB::transaction(function () use ($r, $data) {
            DB::table('public_experience_settings')->insertOrIgnore(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('public_experience_settings')->where('id', 1)->lockForUpdate()->first();
            abort_if((int) $row->version !== (int) $data['version'], 409);
            $values = array_diff_key($data, ['version' => true]);
            $values['version'] = $row->version + 1;
            $values['updated_at'] = now();
            DB::table('public_experience_settings')->where('id', 1)->update($values);
            DB::table('public_experience_audits')->insert(['actor_id' => $r->user()->id, 'action' => 'settings',
                'version' => $values['version'], 'payload' => json_encode($data, JSON_THROW_ON_ERROR), 'created_at' => now()]);
        });

        return back()->with('success', __('public_experience.saved'));
    }

    private function authorizeLocation(Request $r, Opportunity $o): void
    {
        if ($r->routeIs('admin.*')) {
            $this->admin($r);

            return;
        }
        $w = app(CompanyMarketplaceService::class)->actor($r->user(), true);
        abort_unless((int) $o->company_workspace_id === (int) $w->id, 404);
        abort_unless($w->status === 'active' && (int) $r->user()->customer_id === (int) $w->customer_id
            && Customer::whereKey($w->customer_id)->where('is_active', true)->exists(), 403);
    }

    public function location(Request $r, Opportunity $opportunity, PublicExperience $service)
    {
        $this->authorizeLocation($r, $opportunity);
        abort_unless($service->marineQuery()->whereKey($opportunity->id)->exists(), 404);

        return response()->view('public-experience.location', ['opportunity' => $opportunity,
            'location' => $service->location($opportunity), 'admin' => $r->routeIs('admin.*')])->header('Cache-Control', 'no-store');
    }

    public function saveLocation(Request $r, Opportunity $opportunity, PublicExperience $service)
    {
        $this->authorizeLocation($r, $opportunity);
        $data = $r->validate(['version' => 'required|integer|min:0', 'is_public' => 'required|boolean',
            'latitude' => 'required_if:is_public,1|nullable|numeric|between:-90,90',
            'longitude' => 'required_if:is_public,1|nullable|numeric|between:-180,180',
            'label' => 'required_if:is_public,1|nullable|string|max:120', 'confirm_public' => 'required_if:is_public,1|accepted_if:is_public,1']);
        DB::transaction(function () use ($r, $opportunity, $service, $data) {
            // Follow workspace -> user -> member -> customer -> opportunity lock order.
            if (! $r->routeIs('admin.*')) {
                $w = CompanyWorkspace::whereKey($opportunity->company_workspace_id)->lockForUpdate()->firstOrFail();
                $user = User::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
                $r->setUserResolver(fn () => $user);
                CompanyMember::where('company_workspace_id', $w->id)->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
                Customer::whereKey($w->customer_id)->lockForUpdate()->firstOrFail();
            }
            $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            $this->authorizeLocation($r, $o);
            abort_unless($service->marineQuery()->whereKey($o->id)->exists(), 404);
            DB::table('opportunity_map_locations')->insertOrIgnore(['opportunity_id' => $o->id, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('opportunity_map_locations')->where('opportunity_id', $o->id)->lockForUpdate()->first();
            abort_if((int) $row->version !== (int) $data['version'], 409);
            $visible = $r->boolean('is_public');
            $values = ['version' => $row->version + 1, 'is_public' => $visible,
                'latitude' => $visible ? round((float) $data['latitude'], 2) : null,
                'longitude' => $visible ? round((float) $data['longitude'], 2) : null,
                'label' => $visible ? $data['label'] : null,
                'confirmed_by' => $visible ? $r->user()->id : null, 'confirmed_at' => $visible ? now() : null, 'updated_at' => now()];
            DB::table('opportunity_map_locations')->where('id', $row->id)->update($values);
            DB::table('public_experience_audits')->insert(['actor_id' => $r->user()->id, 'action' => 'map_location',
                'opportunity_id' => $o->id, 'version' => $values['version'], 'payload' => json_encode($values, JSON_THROW_ON_ERROR), 'created_at' => now()]);
        });

        return back()->with('success', __('public_experience.saved'));
    }
}
