<?php

namespace App\Http\Controllers;

use App\Models\MarineBoardInquiry;
use App\Models\MarineCargoListing;
use App\Models\MarineVesselListing;
use App\Models\Port;
use App\Rules\NoPublicContactDetails;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class MarineBoardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cargo_type' => ['nullable', Rule::in(MarineCargoListing::CARGO_TYPES)],
            'loading_port_id' => ['nullable', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
            'discharging_port_id' => ['nullable', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
            'vessel_type' => ['nullable', Rule::in(MarineVesselListing::VESSEL_TYPES)],
            'current_port_id' => ['nullable', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
            'dwt_min' => ['nullable', 'numeric', 'min:0'],
            'open_from' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $cargoQuery = MarineCargoListing::query()
            ->select(['id', 'public_reference', 'cargo_type', 'public_description', 'quantity', 'loading_port_id', 'discharging_port_id', 'laycan_from', 'laycan_to', 'readiness_date', 'published_at'])
            ->where('status', 'published')->whereNotNull('published_at')
            ->whereDate('laycan_to', '>=', now('Asia/Dubai')->toDateString())
            ->where(function ($query) { $query->whereNull('closed_at')->orWhere('closed_at', '>', now()); })
            ->with(['loadingPort:id,name,country_code', 'dischargingPort:id,name,country_code']);
        if (! empty($filters['cargo_type'])) $cargoQuery->where('cargo_type', $filters['cargo_type']);
        if (! empty($filters['loading_port_id'])) $cargoQuery->where('loading_port_id', $filters['loading_port_id']);
        if (! empty($filters['discharging_port_id'])) $cargoQuery->where('discharging_port_id', $filters['discharging_port_id']);
        $cargoes = $cargoQuery->orderByDesc('published_at')->orderByDesc('id')->paginate(8, ['*'], 'cargoes_page')->withQueryString();

        $vesselQuery = MarineVesselListing::query()
            ->select(['id', 'public_reference', 'vessel_type', 'dwt_capacity', 'current_port_id', 'current_position', 'open_date', 'preferred_trading_area', 'published_at'])
            ->where('status', 'published')->whereNotNull('published_at')
            ->whereDate('open_date', '>=', now('Asia/Dubai')->toDateString())
            ->where(function ($query) { $query->whereNull('closed_at')->orWhere('closed_at', '>', now()); })
            ->with(['currentPort:id,name,country_code']);
        if (! empty($filters['vessel_type'])) $vesselQuery->where('vessel_type', $filters['vessel_type']);
        if (! empty($filters['current_port_id'])) $vesselQuery->where('current_port_id', $filters['current_port_id']);
        if (! empty($filters['dwt_min'])) $vesselQuery->where('dwt_capacity', '>=', $filters['dwt_min']);
        if (! empty($filters['open_from'])) $vesselQuery->whereDate('open_date', '>=', $filters['open_from']);
        $vessels = $vesselQuery->orderBy('open_date')->orderByDesc('id')->paginate(8, ['*'], 'vessels_page')->withQueryString();

        return view('maritime-board.index', [
            'cargoes' => $cargoes,
            'vessels' => $vessels,
            'ports' => Port::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']),
            'filters' => $filters,
            'activeTab' => $request->query('tab') === 'vessels' ? 'vessels' : 'cargoes',
        ]);
    }

    public function create(string $kind): View
    {
        abort_unless(in_array($kind, ['cargo', 'vessel'], true), 404);

        return view('maritime-board.form', [
            'kind' => $kind,
            'ports' => Port::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']),
        ]);
    }

    public function store(Request $request, string $kind): RedirectResponse
    {
        abort_unless(in_array($kind, ['cargo', 'vessel'], true), 404);

        $common = [
            'company_name' => ['required', 'string', 'max:200'],
            'contact_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'terms_accepted' => ['accepted'],
        ];

        if ($kind === 'cargo') {
            $data = $request->validate($common + [
                'cargo_type' => ['required', Rule::in(MarineCargoListing::CARGO_TYPES)],
                'description' => ['required', 'string', 'min:20', 'max:5000'],
                'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999'],
                'loading_port_id' => ['required', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
                'discharging_port_id' => ['required', 'integer', 'different:loading_port_id', Rule::exists('ports', 'id')->where('is_active', true)],
                'laycan_from' => ['required', 'date_format:Y-m-d'],
                'laycan_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:laycan_from', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
                'readiness_date' => ['nullable', 'date_format:Y-m-d'],
            ]);
            unset($data['terms_accepted']);
            $data += [
                'public_reference' => 'CG-'.Str::upper(Str::random(10)),
                'user_id' => $request->user()?->id,
                'status' => 'pending_review',
                'terms_accepted_at' => now(),
            ];
            $listing = MarineCargoListing::create($data);
            NotificationService::admins(
                'marine_board.cargo.submitted',
                __('maritime_board.admin_new_cargo'),
                __('maritime_board.admin_new_cargo_message', ['reference' => $listing->public_reference]),
                $listing,
                ['url' => route('admin.maritime-board.index')]
            );
        } else {
            $data = $request->validate($common + [
                'vessel_type' => ['required', Rule::in(MarineVesselListing::VESSEL_TYPES)],
                'dwt_capacity' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
                'current_port_id' => ['nullable', 'integer', Rule::exists('ports', 'id')->where('is_active', true)],
                'current_position' => ['nullable', 'string', 'max:180', 'required_without:current_port_id', new NoPublicContactDetails()],
                'open_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
                'preferred_trading_area' => ['required', 'string', 'max:255', new NoPublicContactDetails()],
            ]);
            unset($data['terms_accepted']);
            $data += [
                'public_reference' => 'VS-'.Str::upper(Str::random(10)),
                'user_id' => $request->user()?->id,
                'status' => 'pending_review',
                'terms_accepted_at' => now(),
            ];
            $listing = MarineVesselListing::create($data);
            NotificationService::admins(
                'marine_board.vessel.submitted',
                __('maritime_board.admin_new_vessel'),
                __('maritime_board.admin_new_vessel_message', ['reference' => $listing->public_reference]),
                $listing,
                ['url' => route('admin.maritime-board.index')]
            );
        }

        return redirect()->route('maritime-board.index')
            ->with('marine_board_submitted', __('maritime_board.submitted_for_review'));
    }

    public function inquiryForm(string $kind, string $reference): View
    {
        [$listing, $title] = $this->publishedListing($kind, $reference);

        return view('maritime-board.inquiry', compact('kind', 'listing', 'title'));
    }

    public function inquire(Request $request, string $kind, string $reference): RedirectResponse
    {
        [$listing] = $this->publishedListing($kind, $reference);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'company_name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'min:20', 'max:4000'],
            'terms_accepted' => ['accepted'],
        ]);
        unset($data['terms_accepted']);
        $data += [
            'public_reference' => 'IN-'.Str::upper(Str::random(10)),
            'user_id' => $request->user()?->id,
            'status' => 'new',
            'terms_accepted_at' => now(),
        ];
        if ($kind === 'cargo') {
            $data['cargo_listing_id'] = $listing->id;
        } else {
            $data['vessel_listing_id'] = $listing->id;
        }
        $inquiry = MarineBoardInquiry::create($data);
        NotificationService::admins(
            'marine_board.inquiry.received',
            __('maritime_board.admin_new_inquiry'),
            __('maritime_board.admin_new_inquiry_message', ['reference' => $inquiry->public_reference]),
            $inquiry,
            ['url' => route('admin.maritime-board.index')]
        );

        return redirect()->route('maritime-board.index', ['tab' => $kind === 'cargo' ? 'cargoes' : 'vessels'])
            ->with('marine_board_submitted', __('maritime_board.inquiry_received'));
    }

    /** @return array{0: MarineCargoListing|MarineVesselListing, 1: string} */
    private function publishedListing(string $kind, string $reference): array
    {
        if ($kind === 'cargo') {
            $listing = MarineCargoListing::query()->select(['id', 'public_reference', 'cargo_type', 'public_description', 'quantity', 'loading_port_id', 'discharging_port_id', 'laycan_from', 'laycan_to', 'readiness_date', 'published_at'])->where('public_reference', $reference)->where('status', 'published')->whereNotNull('published_at')->whereDate('laycan_to', '>=', now('Asia/Dubai')->toDateString())->where(function ($query) { $query->whereNull('closed_at')->orWhere('closed_at', '>', now()); })->with(['loadingPort', 'dischargingPort'])->firstOrFail();
            return [$listing, __('maritime_board.available_cargo')];
        }
        if ($kind === 'vessel') {
            $listing = MarineVesselListing::query()->select(['id', 'public_reference', 'vessel_type', 'dwt_capacity', 'current_port_id', 'current_position', 'open_date', 'preferred_trading_area', 'published_at'])->where('public_reference', $reference)->where('status', 'published')->whereNotNull('published_at')->whereDate('open_date', '>=', now('Asia/Dubai')->toDateString())->where(function ($query) { $query->whereNull('closed_at')->orWhere('closed_at', '>', now()); })->with('currentPort')->firstOrFail();
            return [$listing, __('maritime_board.open_vessel')];
        }
        abort(404);
    }
}
