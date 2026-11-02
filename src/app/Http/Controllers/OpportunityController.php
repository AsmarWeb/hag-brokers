<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Services\Platform\CompanyMarketplaceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class OpportunityController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $service)
    {
        $query = $service->publicQuery()->with(['originPort', 'destinationPort', 'media']);
        if ($request->filled('search')) {
            $term = $request->string('search')->trim()->toString();
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')->orWhere('description', 'like', '%'.$term.'%'));
        }
        foreach (['type' => 'opportunity_type', 'category' => 'category', 'country' => 'country'] as $input => $field) {
            if ($request->filled($input)) {
                $query->where($field, $request->string($input)->toString());
            }
        }
        $opportunities = $query->latest('published_at')->paginate(12)->withQueryString();

        return view('opportunities.index', compact('opportunities'));
    }

    public function create(Request $request, CompanyMarketplaceService $service)
    {
        $service->actor($request->user(), true);

        return view('opportunities.create', ['requestKey' => (string) Str::uuid()]);
    }

    private function publicationData(Request $request): array
    {
        $data = $request->validate([
            'request_key' => ['required', 'uuid'], 'title' => ['required', 'string', 'max:255'],
            'opportunity_type' => ['required', 'in:business,investment,trade,procurement,distribution,logistics,partnership,vessel_brokerage,other'],
            'description' => ['required', 'string', 'min:20', 'max:20000'], 'category' => ['nullable', 'string', 'max:100'],
            'cargo_type' => ['nullable', 'string', 'max:255'], 'container_type' => ['nullable', 'string', 'max:255'],
            'origin_port_id' => ['nullable', 'integer', 'exists:ports,id'], 'destination_port_id' => ['nullable', 'integer', 'exists:ports,id'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'required_capital' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'min_investment' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'], 'commission_type' => ['required', 'in:percentage,fixed'],
            'commission_rate' => ['nullable', 'decimal:0,2', 'min:0', ($request->input('commission_type') === 'percentage' ? 'max:100' : 'max:999999.99')],
            'expected_close_date' => ['nullable', 'date_format:Y-m-d'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'notes' => ['nullable', 'string', 'max:5000'], 'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:20480'],
            'vessel' => ['required_if:opportunity_type,vessel_brokerage', 'array'],
            'vessel.listing_type' => ['required_if:opportunity_type,vessel_brokerage', 'in:sale,wanted,charter'],
            'vessel.vessel_category' => ['required_if:opportunity_type,vessel_brokerage', 'in:yacht,commercial_vessel,boat'],
            'vessel.vessel_type' => ['nullable', 'string', 'max:80'], 'vessel.vessel_name' => ['nullable', 'string', 'max:255'],
            'vessel.builder' => ['nullable', 'string', 'max:255'], 'vessel.year_built' => ['nullable', 'integer', 'between:1800,2100'],
            'vessel.length_m' => ['nullable', 'numeric', 'gt:0', 'max:99999'], 'vessel.imo_number' => ['nullable', 'digits:7'],
        ]);
        unset($data['request_key'],$data['media']);
        if ($data['opportunity_type'] !== 'vessel_brokerage') {
            unset($data['vessel']);
        } elseif (isset($data['vessel'])) {
            $data['vessel'] = array_intersect_key($data['vessel'], array_flip(['listing_type', 'vessel_category', 'vessel_type', 'vessel_name', 'builder', 'year_built', 'length_m', 'imo_number']));
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

    public function show(Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        abort_unless($service->isPublic($opportunity), 404);
        $opportunity->load(['originPort', 'destinationPort', 'media']);

        return view('opportunities.show', compact('opportunity'));
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

        return redirect()->route('company.marketplace')->with('success',__('market.delivered'));
    }
}
