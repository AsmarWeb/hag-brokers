<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyWorkspace;
use App\Models\Opportunity;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use App\Services\Platform\OpportunityInput;
use App\Services\Platform\OpportunitySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyOpportunityController extends Controller
{
    private const EDITABLE = ['title', 'description', 'category', 'country', 'city', 'notes', 'commercial_details'];

    private const IMMUTABLE = ['customer_id', 'company_workspace_id', 'submitted_by', 'slug', 'marketplace_state', 'visibility', 'verification_status', 'published_at', 'stage', 'probability', 'assigned_to', 'contact_id'];

    private const FINANCIAL = ['opportunity_type', 'currency', 'estimated_value', 'required_capital', 'min_investment', 'commission_type', 'commission_rate', 'origin_port_id', 'destination_port_id', 'cargo_type', 'container_type', 'expected_close_date', 'expires_at'];

    private function owned(Request $r, string $id, CompanyMarketplaceService $service): Opportunity
    {
        $w = $service->actor($r->user(), true);

        return Opportunity::where('company_workspace_id', $w->id)->whereKey($id)->firstOrFail();
    }

    private function canonical(array $data): array
    {
        if (! array_is_list($data)) {
            ksort($data);
        }
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->canonical($value);
            }
        }

        return $data;
    }

    private function revision(Opportunity $o): string
    {
        // Include complete stored business state, marine data and media identity: safe even for same-second edits.
        $o->loadMissing(['vessel', 'media', 'moderation']);

        return hash('sha256', json_encode($this->canonical(['listing' => $o->getAttributes(),
            'vessel' => $o->vessel?->getAttributes(), 'media' => $o->media->map(fn ($m) => $m->getAttributes())->all(),
            'moderation' => $o->moderation?->getAttributes()]), JSON_THROW_ON_ERROR));
    }

    private function representation(Opportunity $o, CompanyMarketplaceService $service): array
    {
        $o->loadMissing(['media', 'vessel', 'moderation']);

        return $o->only(array_merge(['id', 'slug'], self::EDITABLE, self::FINANCIAL)) + [
            'marketplace_state' => $o->marketplace_state, 'is_public' => $service->isPublic($o),
            'administratively_hidden' => (bool) $o->moderation?->hidden, 'reviewed' => $o->verification_status === 'verified',
            'revision' => $this->revision($o), 'published_at' => $o->published_at?->utc()->toIso8601String(),
            'vessel' => $o->vessel?->only(OpportunityCatalog::VESSEL_FIELDS),
            'media' => $o->media->map(fn ($m) => ['id' => $m->id, 'type' => $m->type, 'url' => Storage::disk('public')->url($m->path)])->values(),
        ];
    }

    public function index(Request $r, CompanyMarketplaceService $service)
    {
        $w = $service->actor($r->user(), true);
        $d = $r->validate(['per_page' => ['nullable', 'integer', 'between:1,50'], 'page' => ['nullable', 'integer', 'between:1,10000'],
            'state' => ['nullable', 'in:published,closed']]);
        $q = Opportunity::where('company_workspace_id', $w->id)->with(['media', 'vessel', 'moderation']);
        if (isset($d['state'])) {
            $q->where('marketplace_state', $d['state']);
        }
        $p = $q->latest('id')->paginate($d['per_page'] ?? 20);
        $usage = $service->usage($w);

        return response()->json(['data' => $p->getCollection()->map(fn ($o) => $this->representation($o, $service)),
            'meta' => ['current_page' => $p->currentPage(), 'per_page' => $p->perPage(), 'total' => $p->total(), 'last_page' => $p->lastPage()],
            'usage' => ['active' => $usage['active'], 'active_limit' => $usage['plan']['active_opportunities']]]);
    }

    public function show(Request $r, string $opportunityId, CompanyMarketplaceService $service)
    {
        return response()->json(['data' => $this->representation($this->owned($r, $opportunityId, $service), $service)]);
    }

    public function store(Request $r, CompanyMarketplaceService $service, OpportunityInput $input, OpportunitySubmission $submission)
    {
        $service->actor($r->user(), true);
        // API clients must supply a stable expiry date so retries across midnight cannot change the request hash.
        $r->validate(['expires_at' => ['required', 'date_format:Y-m-d']] + array_fill_keys(self::IMMUTABLE, ['prohibited']));
        $data = $input->publicationData($r);
        $result = $submission->publish($r, $data, $r->string('request_key')->toString(), $service);
        $o = $result['opportunity']->fresh();

        return response()->json(['data' => $this->representation($o, $service), 'meta' => ['created' => $result['created']]], $result['created'] ? 201 : 200);
    }

    public function update(Request $r, string $opportunityId, CompanyMarketplaceService $service, OpportunityInput $input)
    {
        $this->owned($r, $opportunityId, $service);
        $r->validate(['revision' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D']] + array_fill_keys(array_merge(self::IMMUTABLE, self::FINANCIAL, ['media', 'request_key']), ['prohibited']));
        $patch = $r->only(array_merge(self::EDITABLE, ['vessel']));
        $o = $this->locked($r, $opportunityId, $service, function ($o) use ($r, $service, $input, $patch) {
            // Omitted top-level fields retain their current values; nested details/vessel are complete replacements when supplied.
            $current = $o->only(self::EDITABLE);
            if ($o->vessel) {
                $current['vessel'] = $o->vessel->only(OpportunityCatalog::VESSEL_FIELDS);
            }
            $r->merge(array_replace($current, $patch));
            $data = $input->updateData($r, $o);

            return $service->update($r->user(), $o, $data);
        });

        return response()->json(['data' => $this->representation($o->fresh(), $service)]);
    }

    public function close(Request $r, string $opportunityId, CompanyMarketplaceService $service)
    {
        $this->owned($r, $opportunityId, $service);
        $r->validate(['revision' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D'], 'confirmation' => ['required', 'accepted']]);
        $o = $this->locked($r, $opportunityId, $service, function ($o) use ($r, $service) {
            $service->close($r->user(), $o);

            return $o->fresh();
        });

        return response()->json(['data' => $this->representation($o, $service)]);
    }

    private function locked(Request $r, string $id, CompanyMarketplaceService $service, \Closure $action): Opportunity
    {
        return DB::transaction(function () use ($r, $id, $service, $action) {
            $w = $service->actor($r->user(), true);
            CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
            $service->actor($r->user(), true);
            $o = Opportunity::where('company_workspace_id', $w->id)->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless(hash_equals($this->revision($o),$r->string('revision')->toString()),409,'The opportunity changed. Reload before retrying.');

            return $action($o);
        }, 3);
    }
}
