<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\FreightSailing;
use App\Models\Vessel;
use App\Services\Freight\SailingCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminFreightSailingController extends Controller
{
    public function index()
    {
        return view('admin.freight-sailings.index', [
            'sailings' => FreightSailing::with(['originPort', 'destinationPort', 'carrier', 'vessel'])
                ->latest('departure_at')->latest('id')->paginate(20),
        ]);
    }

    public function create(SailingCatalog $catalog)
    {
        return $this->form(new FreightSailing(['status' => 'draft']), $catalog);
    }

    public function edit(FreightSailing $sailing, SailingCatalog $catalog)
    {
        return $this->form($sailing, $catalog);
    }

    private function form(FreightSailing $sailing, SailingCatalog $catalog)
    {
        return view('admin.freight-sailings.form', [
            'sailing' => $sailing,
            'ports' => $catalog->ports()->orderBy('name')->get(),
            'vessels' => Vessel::whereHas('carrier', fn ($query) => $query->where('is_active', true))->orderBy('name')->get(['id', 'carrier_id', 'name', 'imo']),
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, SailingCatalog $catalog)
    {
        return $this->save($request, new FreightSailing, $catalog);
    }

    public function update(Request $request, FreightSailing $sailing, SailingCatalog $catalog)
    {
        return $this->save($request, $sailing, $catalog);
    }

    public function cancel(FreightSailing $sailing)
    {
        $sailing->update(['status' => 'cancelled']);

        return redirect()->route('admin.freight-sailings.index')->with('success', __('sailings.saved'));
    }

    private function save(Request $request, FreightSailing $sailing, SailingCatalog $catalog)
    {
        $ports = $catalog->ports()->pluck('id')->all();
        $data = $request->validate([
            'origin_port_id' => ['required', 'integer', Rule::in($ports)],
            'destination_port_id' => ['required', 'integer', 'different:origin_port_id', Rule::in($ports)],
            'carrier_id' => ['required', 'integer', Rule::exists('carriers', 'id')->where('is_active', true)],
            'vessel_id' => ['nullable', 'integer', Rule::exists('vessels', 'id')->where('carrier_id', $request->integer('carrier_id'))],
            'vessel_name' => ['required_without:vessel_id', 'nullable', 'string', 'max:120'],
            'voyage_number' => ['required', 'string', 'max:60'],
            'departure_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'arrival_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:departure_at'],
            'cargo_cutoff_at' => ['required', 'date_format:Y-m-d\TH:i', 'before:departure_at'],
            'container_types' => ['required', 'array', 'list', 'min:1', 'max:3'],
            'container_types.*' => ['required', 'distinct', Rule::in(FreightSailing::CONTAINER_TYPES)],
            'status' => ['required', Rule::in(FreightSailing::STATUSES)],
            'revision' => [$sailing->exists ? 'required' : 'nullable', 'string', 'size:64'],
        ]);
        $revision = $data['revision'] ?? null;
        unset($data['revision']);
        foreach (['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field) {
            $data[$field] = CarbonImmutable::parse($data[$field], 'Asia/Dubai')->utc();
        }
        if ($data['status'] === 'published' && $data['cargo_cutoff_at']->isPast()) {
            throw ValidationException::withMessages(['cargo_cutoff_at' => __('sailings.invalid_selection')]);
        }

        try {
            DB::transaction(function () use ($sailing, $data, $revision) {
                $target = $sailing->exists ? FreightSailing::lockForUpdate()->findOrFail($sailing->id) : $sailing;
                if ($target->exists && ! hash_equals(self::revision($target), $revision ?? '')) {
                    throw ValidationException::withMessages(['revision' => __('sailings.stale')]);
                }
                $vesselId = $data['vessel_id'] ?? $target->vessel_id;
                if ($vesselId) {
                    $vessel = Vessel::whereKey($vesselId)->where('carrier_id', $data['carrier_id'])->lockForUpdate()->first();
                    if (! $vessel || mb_strlen($vessel->name) > 120) {
                        throw ValidationException::withMessages(['vessel_id' => __('sailing_import.invalid_vessel')]);
                    }
                    $data['vessel_id'] = $vessel->id;
                    $data['vessel_name'] = $vessel->name;
                }
                $target->fill($data)->save();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['voyage_number' => __('sailings.duplicate')]);
        }

        return redirect()->route('admin.freight-sailings.index')->with('success', __('sailings.saved'));
    }

    public static function revision(FreightSailing $sailing): string
    {
        return hash('sha256', json_encode($sailing->getRawOriginal(), JSON_THROW_ON_ERROR));
    }
}
