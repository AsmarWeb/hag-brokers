<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\Port;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityCatalog;
use App\Services\Seo\PublicSeo;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class OpportunityController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $service)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(OpportunityCatalog::TYPES)],
            'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'country' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', Rule::in(OpportunityCatalog::SUBCATEGORIES[$request->input('category')] ?? [])],
            'vessel_category' => ['nullable', 'in:yacht,boat,commercial_vessel'],
            'currency' => [Rule::requiredIf($request->filled('min_price') || $request->filled('max_price') || in_array($request->input('sort'), ['price_asc', 'price_desc'], true)), 'nullable', Rule::in(OpportunityCatalog::CURRENCIES)],
            'min_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'max_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99', Rule::when($request->filled('min_price'), 'gte:min_price')],
            'sort' => ['nullable', 'in:newest,oldest,price_asc,price_desc'],
        ]);
        $query = $service->publicQuery()->with(['customer', 'media', 'vessel']);
        if ($term = trim($filters['search'] ?? '')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')->orWhere('description', 'like', '%'.$term.'%')->orWhere('commercial_details->product_name', 'like', '%'.$term.'%'));
        }
        foreach (['type' => 'opportunity_type', 'category' => 'category'] as $input => $field) {
            if ($filters[$input] ?? null) {
                $query->where($field, $filters[$input]);
            }
        }
        if ($country = trim($filters['country'] ?? '')) {
            if (in_array(mb_strtolower($country), ['ae', 'uae', 'united arab emirates', 'الإمارات العربية المتحدة'], true)) {
                $query->whereIn('country', ['AE', 'UAE', 'United Arab Emirates', 'الإمارات العربية المتحدة']);
            } else {
                $query->where('country', $country);
            }
        }
        if ($filters['subcategory'] ?? null) {
            $query->where('commercial_details->subcategory', $filters['subcategory']);
        }
        if ($filters['vessel_category'] ?? null) {
            $query->whereHas('vessel', fn ($q) => $q->where('vessel_category', $filters['vessel_category']));
        }
        if ($filters['currency'] ?? null) {
            $query->where('currency', $filters['currency']);
        }
        if (isset($filters['min_price'])) {
            $query->where('estimated_value', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('estimated_value', '<=', $filters['max_price']);
        }
        $sort = $filters['sort'] ?? 'newest';
        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $query->orderByRaw('case when estimated_value is null then 1 else 0 end')->orderBy('estimated_value', $sort === 'price_asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc');
        }
        $opportunities = $query->orderByDesc('id')->paginate(12)->withQueryString();

        return view('opportunities.index', compact('opportunities'));
    }

    public function create(Request $request, CompanyMarketplaceService $service)
    {
        $service->actor($request->user(), true);

        return view('opportunities.create', ['requestKey' => (string) Str::uuid(), 'ports' => Port::orderBy('name')->get(['id', 'name'])]);
    }

    private function publicationData(Request $request): array
    {
        $marine = OpportunityCatalog::isMarine((string) $request->input('opportunity_type'), $request->input('category'), (array) $request->input('commercial_details', []));
        $data = $request->validate([
            'request_key' => ['required', 'uuid'], 'title' => ['required', 'string', 'max:255'],
            'opportunity_type' => ['required', Rule::in(OpportunityCatalog::TYPES)],
            'description' => ['required', 'string', 'min:20', 'max:20000'], 'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'cargo_type' => ['nullable', 'string', 'max:255'], 'container_type' => ['nullable', 'string', 'max:255'],
            'origin_port_id' => ['nullable', 'integer', 'exists:ports,id'], 'destination_port_id' => ['nullable', 'integer', 'exists:ports,id'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'required_capital' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'min_investment' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'], 'commission_type' => ['nullable', 'in:percentage,fixed'],
            'commission_rate' => ['nullable', 'decimal:0,2', 'min:0', (($request->input('commission_type') ?? 'percentage') === 'percentage' ? 'max:100' : 'max:999999.99')],
            'expected_close_date' => ['nullable', 'date_format:Y-m-d'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'notes' => ['nullable', 'string', 'max:5000'], 'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:20480'],
        ] + OpportunityCatalog::detailRules($request->input('category')) + OpportunityCatalog::vesselRules($marine, $request->input('commercial_details.subcategory')));
        if (collect($request->file('media', []))->sum(fn ($file) => $file->getSize()) > 20 * 1024 * 1024) {
            throw ValidationException::withMessages(['media' => __('trade.photo_hint')]);
        }
        $data['commission_type'] = $data['commission_type'] ?? 'percentage';
        unset($data['request_key'],$data['media']);
        if (! $marine) {
            unset($data['vessel']);
        } elseif (isset($data['vessel'])) {
            $data['vessel'] = array_intersect_key($data['vessel'], array_flip(OpportunityCatalog::VESSEL_FIELDS));
        }
        $data['currency'] = strtoupper($data['currency']);
        // The submitted calendar date means the whole day in Dubai.
        $data['expires_at'] = CarbonImmutable::parse($data['expires_at'] ?? now('Asia/Dubai')->addDays(30)->toDateString(), 'Asia/Dubai')->endOfDay()->utc()->format('Y-m-d H:i:s');

        return $data;
    }

    public function store(Request $request, CompanyMarketplaceService $service)
    {
        $service->actor($request->user(), true);
        $data = $this->publicationData($request);
        $media = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $mime = $file->getMimeType();
                $path = $file->storeAs('opportunities/company', (string) Str::uuid().'.'.$file->guessExtension(), 'public');
                if (! $path) {
                    throw new \RuntimeException('Unable to store opportunity media.');
                }
                $media[] = ['path' => $path, 'type' => str_starts_with($mime, 'video/') ? 'video' : 'image', 'mime_type' => $mime, 'size' => $file->getSize(), 'original_name' => $file->getClientOriginalName(), 'sha256' => hash_file('sha256', $file->getRealPath())];
            }
            $result = $service->publish($request->user(), $data, $request->string('request_key')->toString(), $media);
            if (! $result['created']) {
                foreach ($media as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }
        } catch (\Throwable $e) {
            foreach ($media as $file) {
                Storage::disk('public')->delete($file['path']);
            }throw $e;
        }

        return redirect()->route('company.marketplace')->with('success', __('market.published'));
    }

    public function show(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        abort_unless($service->isPublic($opportunity), 404);
        if (ctype_digit((string) $request->route()->originalParameter('opportunity'))) {
            $parameters = ['opportunity' => $opportunity];
            if (in_array($request->query('lang'), ['ar', 'en'], true)) {
                $parameters['lang'] = $request->query('lang');
            }

            return redirect(app(PublicSeo::class)->url('opportunities.show', $parameters), 301);
        }
        $opportunity->load(['originPort', 'destinationPort', 'media', 'customer', 'vessel']);

        return view($opportunity->isLuxuryMarineListing() ? 'opportunities.marine.show' : 'opportunities.show', compact('opportunity'));
    }

    public function interest(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        $w = $service->actor($request->user());
        abort_unless($service->isPublic($opportunity) && $opportunity->company_workspace_id, 404);
        abort_if((int) $opportunity->customer_id === (int) $w->customer_id, 403);
        $existingInterest = OpportunityInterest::where('opportunity_id', $opportunity->id)->where('company_workspace_id', $w->id)->first();

        return view('opportunities.interest', ['opportunity' => $opportunity, 'existingInterest' => $existingInterest, 'requestKey' => (string) Str::uuid()]);
    }

    public function storeInterest(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        $service->actor($request->user());
        $data = $request->validate(['request_key' => ['required', 'uuid'], 'role' => ['required', 'string', 'max:100'],
            'available_capital' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'], 'message' => ['required', 'string', 'min:10', 'max:5000']]);
        unset($data['request_key']);
        $data['currency'] = strtoupper($data['currency']);
        $service->apply($request->user(), $opportunity, $data, $request->string('request_key')->toString());

        return redirect()->route('company.marketplace')->with('success', __('market.delivered'));
    }
}
