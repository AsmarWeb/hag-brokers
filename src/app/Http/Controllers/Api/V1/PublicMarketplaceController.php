<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyPublicProfile;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicMarketplaceController extends Controller
{
    private function listing(Opportunity $o, bool $detail = false): array
    {
        $data = ['id' => $o->id, 'slug' => $o->slug, 'title' => $o->title, 'type' => $o->opportunity_type,
            'category' => $o->category, 'country' => $o->country, 'city' => $o->city,
            'price' => $o->estimated_value === null ? null : (string) $o->estimated_value, 'currency' => $o->currency,
            'published_at' => $o->published_at?->utc()->toIso8601String(), 'expires_at' => $o->expires_at?->utc()->toIso8601String(),
            'reviewed' => $o->verification_status === 'verified',
            'media' => $o->media->map(fn ($m) => ['type' => $m->type, 'url' => Storage::disk('public')->url($m->path)])->values(),
        ];
        if ($detail) {
            $data['description'] = $o->description;
            $data['commercial_details'] = array_intersect_key((array) $o->commercial_details, array_flip(OpportunityCatalog::FIELDS));
            $data['vessel'] = $o->vessel?->only(OpportunityCatalog::VESSEL_FIELDS);
        }

        return $data;
    }

    public function index(Request $r, CompanyMarketplaceService $market)
    {
        $d = $r->validate(['search' => ['nullable', 'string', 'max:150'], 'type' => ['nullable', Rule::in(OpportunityCatalog::TYPES)],
            'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)], 'country' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,50'], 'page' => ['nullable', 'integer', 'min:1', 'max:10000']]);
        $q = $market->publicQuery()->with('media');
        if ($s = trim($d['search'] ?? '')) {
            $q->where(fn ($q) => $q->where('title', 'like', '%'.$s.'%')->orWhere('description', 'like', '%'.$s.'%'));
        }
        foreach (['type' => 'opportunity_type', 'category' => 'category', 'country' => 'country'] as $input => $field) {
            if (! empty($d[$input])) {
                $q->where($field, $d[$input]);
            }
        }
        $page = $q->orderByDesc('published_at')->orderByDesc('id')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(fn ($o) => $this->listing($o)),
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()]]);
    }

    public function show(string $slug, CompanyMarketplaceService $market)
    {
        $o = $market->publicQuery()->where('slug', $slug)->with(['media', 'vessel'])->firstOrFail();

        return response()->json(['data' => $this->listing($o, true)]);
    }

    public function catalog()
    {
        return response()->json(['data' => ['types' => OpportunityCatalog::TYPES, 'categories' => OpportunityCatalog::CATEGORIES,
            'subcategories' => OpportunityCatalog::SUBCATEGORIES, 'currencies' => OpportunityCatalog::CURRENCIES,
            'units' => OpportunityCatalog::UNITS, 'languages' => ['ar', 'en']]]);
    }

    public function company(string $slug, CompanyMarketplaceService $market)
    {
        $p = CompanyPublicProfile::where('slug', $slug)->where('is_published', true)
            ->whereIn('workspace_id', CompanyWorkspace::where('status', 'active')->whereIn('customer_id', Customer::where('is_active', true)->select('id'))->select('id'))->firstOrFail();

        return response()->json(['data' => ['slug' => $p->slug, 'name' => $p->text('name'), 'about' => $p->text('about'),
            'sector' => $p->sector, 'country' => $p->country, 'city' => $p->city, 'specialties' => $p->specialties,
            'markets' => $p->markets, 'website' => $p->website,
            'opportunities' => $market->publicQuery()->where('company_workspace_id', $p->workspace_id)->with('media')->latest('id')->limit(12)->get()->map(fn ($o) => $this->listing($o))]]);
    }
}
