<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\FreightSailing;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminFreightRateController extends Controller
{
    public function index(Request $request)
    {
        $rates = FreightRate::with(['source', 'carrier', 'originPort', 'destinationPort'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.mb_substr(trim((string) $request->input('q')), 0, 100).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereLike('source_reference', $term)
                        ->orWhereHas('carrier', fn ($x) => $x->whereLike('name', $term))
                        ->orWhereHas('originPort', fn ($x) => $x->whereLike('name', $term)->orWhereLike('unlocode', $term))
                        ->orWhereHas('destinationPort', fn ($x) => $x->whereLike('name', $term)->orWhereLike('unlocode', $term));
                });
            })->latest('id')->paginate(15)->withQueryString();

        return view('admin.freight-rates.index', [
            'sources' => FreightRateSource::withCount('rates')->orderBy('name')->get(),
            'rates' => $rates,
            'ports' => Port::orderBy('name')->get(),
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
            'search' => (string) $request->input('q', ''),
        ]);
    }

    public function create()
    {
        return $this->form(new FreightRate());
    }

    public function edit(FreightRate $rate)
    {
        return $this->form($rate);
    }

    private function form(FreightRate $rate)
    {
        return view('admin.freight-rates.form', [
            'rate' => $rate,
            'sources' => FreightRateSource::orderBy('name')->get(),
            'ports' => Port::orderBy('name')->get(),
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
            'sailings' => FreightSailing::where('status', 'published')->latest('departure_at')->limit(200)->get(),
        ]);
    }

    public function storeSource(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:freight_rate_sources,code'],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['hag', 'carrier', 'partner', 'provider', 'manual'])],
            'tier' => ['required', Rule::in(['free', 'trial', 'paid', 'internal'])],
            'markup_percent' => ['required', 'numeric', 'min:0', 'max:500'],
            'markup_fixed' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'capabilities' => ['nullable', 'array', 'max:12'],
            'capabilities.*' => ['string', 'max:40'],
            'credentials_json' => ['nullable', 'string', 'max:12000'],
        ]);
        $credentials = $this->credentials($data['credentials_json'] ?? null);
        FreightRateSource::create([
            'code' => strtolower($data['code']), 'name' => $data['name'], 'type' => $data['type'], 'tier' => $data['tier'],
            'markup_percent' => $data['markup_percent'], 'markup_fixed' => $data['markup_fixed'],
            'capabilities' => array_values($data['capabilities'] ?? []), 'credentials' => $credentials,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return back()->with('success', __('freight_rates.source_created'));
    }

    public function updateSource(Request $request, FreightRateSource $source)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['hag', 'carrier', 'partner', 'provider', 'manual'])],
            'tier' => ['required', Rule::in(['free', 'trial', 'paid', 'internal'])],
            'markup_percent' => ['required', 'numeric', 'min:0', 'max:500'],
            'markup_fixed' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'capabilities' => ['nullable', 'array', 'max:12'], 'capabilities.*' => ['string', 'max:40'],
            'credentials_json' => ['nullable', 'string', 'max:12000'],
        ]);
        $source->fill(collect($data)->except(['credentials_json'])->all());
        if (filled($data['credentials_json'] ?? null)) $source->credentials = $this->credentials($data['credentials_json']);
        $source->save();
        return back()->with('success', __('freight_rates.source_updated'));
    }

    public function toggleSource(FreightRateSource $source)
    {
        $source->update(['is_active' => ! $source->is_active]);
        return back()->with('success', __('freight_rates.source_toggled'));
    }

    public function store(Request $request) { return $this->saveRate($request, new FreightRate()); }
    public function update(Request $request, FreightRate $rate) { return $this->saveRate($request, $rate); }

    public function destroy(FreightRate $rate)
    {
        if ($rate->status === 'published') throw ValidationException::withMessages(['rate' => __('freight_rates.unpublish_before_delete')]);
        $rate->delete();
        return redirect()->route('admin.freight-rates.index')->with('success', __('freight_rates.rate_deleted'));
    }

    private function saveRate(Request $request, FreightRate $rate)
    {
        $data = $request->validate([
            'source_id' => ['required', 'integer', Rule::exists('freight_rate_sources', 'id')],
            'carrier_id' => ['nullable', 'integer', Rule::exists('carriers', 'id')],
            'freight_sailing_id' => ['nullable', 'integer', Rule::exists('freight_sailings', 'id')],
            'origin_port_id' => ['required', 'integer', Rule::exists('ports', 'id')],
            'destination_port_id' => ['required', 'integer', 'different:origin_port_id', Rule::exists('ports', 'id')],
            'container_type' => ['required', Rule::in(FreightRate::CONTAINER_TYPES)],
            'min_quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'max_quantity' => ['nullable', 'integer', 'gte:min_quantity', 'max:10000'],
            'supplier_unit_cost' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'markup_percent' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'markup_fixed' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'currency' => ['required', 'regex:/^[A-Z]{3}$/'],
            'surcharge_items_json' => ['nullable', 'string', 'max:12000'],
            'included_charges_text' => ['nullable', 'string', 'max:3000'],
            'excluded_charges_text' => ['nullable', 'string', 'max:3000'],
            'availability' => ['required', Rule::in(['on_request', 'limited', 'available'])],
            'booking_mode' => ['required', Rule::in(['request', 'manual_confirmation'])],
            'rate_type' => ['required', Rule::in(['spot_rate', 'contract_rate', 'partner_rate', 'manual_rate'])],
            'status' => ['required', Rule::in(['draft', 'published', 'paused'])],
            'source_reference' => ['nullable', 'string', 'max:120'],
            'quoted_at' => ['nullable', 'date'], 'valid_from' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date', 'after:valid_from'],
            'terms' => ['nullable', 'string', 'max:5000'],
        ]);
        $source = FreightRateSource::findOrFail($data['source_id']);
        if ($data['status'] === 'published' && ! $source->is_active) throw ValidationException::withMessages(['source_id' => __('freight_rates.source_must_be_active')]);
        $data['surcharge_items'] = $this->decodeSurcharges($data['surcharge_items_json'] ?? null);
        $data['included_charges'] = $this->splitCharges($data['included_charges_text'] ?? null);
        $data['excluded_charges'] = $this->splitCharges($data['excluded_charges_text'] ?? null);
        unset($data['surcharge_items_json'], $data['included_charges_text'], $data['excluded_charges_text']);
        $percent = $data['markup_percent'] ?? (float) $source->markup_percent;
        $fixed = $data['markup_fixed'] ?? (float) $source->markup_fixed;
        $customer = round((float) $data['supplier_unit_cost'] * (1 + $percent / 100) + $fixed, 2);
        unset($data['markup_percent'], $data['markup_fixed']);
        $rate->fill($data)->forceFill([
            'markup_percent' => $percent, 'markup_fixed' => $fixed,
            'customer_unit_price' => $customer, 'quoted_at' => $data['quoted_at'] ?? now(),
        ])->save();
        return redirect()->route('admin.freight-rates.index')->with('success', __('freight_rates.rate_saved'));
    }

    private function decodeSurcharges(?string $json): ?array
    {
        if (blank($json)) return null;
        try { $items = json_decode($json, true, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw ValidationException::withMessages(['surcharge_items_json' => __('freight_rates.surcharges_invalid')]); }
        if (! is_array($items) || ! array_is_list($items) || count($items) > 30) throw ValidationException::withMessages(['surcharge_items_json' => __('freight_rates.surcharges_invalid')]);
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['name'], $item['amount']) || ! is_numeric($item['amount']) || (float) $item['amount'] < 0 || mb_strlen((string) $item['name']) > 100) throw ValidationException::withMessages(['surcharge_items_json' => __('freight_rates.surcharges_invalid')]);
        }
        return $items;
    }

    private function splitCharges(?string $value): ?array
    {
        if (blank($value)) return null;
        return array_values(array_filter(array_map(fn ($item) => mb_substr(trim($item), 0, 100), explode(',', $value))));
    }

    private function credentials(?string $json): ?array
    {
        if (blank($json)) return null;
        try { $value = json_decode($json, true, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw ValidationException::withMessages(['credentials_json' => __('freight_rates.credentials_invalid')]); }
        if (! is_array($value) || array_is_list($value)) throw ValidationException::withMessages(['credentials_json' => __('freight_rates.credentials_object_required')]);
        return $value;
    }
}
