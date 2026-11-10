<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierVesselRequest;
use App\Models\Vessel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CarrierVesselRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'imo' => ['required', 'string', 'regex:/^\d{7}$/'],
            'vessel_type' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();
            $partnership = $user->carrierPartnership()->first();
            abort_unless($user->hasVerifiedEmail() && $partnership, 403);
            $carrier = Carrier::lockForUpdate()->find($partnership->carrier_id);
            abort_unless($carrier?->is_active
                && $user->carrierPartnership()->lockForUpdate()->first()?->id === $partnership->id, 403);

            if (CarrierVesselRequest::where('carrier_id', $carrier->id)
                ->where('imo', $data['imo'])->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['imo' => __('carrier_vessels.duplicate')]);
            }

            CarrierVesselRequest::create($data + ['carrier_id' => $carrier->id, 'submitted_by' => $user->id]);
        }, 3);

        return redirect()->route('carrier.fleet')->with('carrier_status', __('carrier_vessels.submitted'));
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);

        return view('admin.vessels.partner-requests', [
            'requests' => CarrierVesselRequest::where('status', 'pending')
                ->with(['carrier', 'registeredVessel.carrier'])->oldest('id')->paginate(20),
        ]);
    }

    public function review(Request $request, CarrierVesselRequest $proposal)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'review_note' => ['required_if:decision,reject', 'nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($request, $proposal, $data) {
                $carrier = Carrier::lockForUpdate()->findOrFail($proposal->carrier_id);
                $pending = CarrierVesselRequest::lockForUpdate()->findOrFail($proposal->id);
                abort_unless($pending->status === 'pending', 409);

                if ($data['decision'] === 'approve') {
                    $vessel = Vessel::where('imo', $pending->imo)->lockForUpdate()->first();
                    if (! $carrier->is_active || ($vessel && (int) $vessel->carrier_id !== $carrier->id)) {
                        throw ValidationException::withMessages(['decision' => __('carrier_vessels.conflict')]);
                    }

                    $vessel ??= new Vessel(['imo' => $pending->imo, 'carrier_id' => $carrier->id]);
                    $vessel->fill(['name' => $pending->name, 'vessel_type' => $pending->vessel_type])->save();
                }

                $pending->forceFill([
                    'status' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'review_note' => $data['review_note'] ?? null,
                ])->save();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['decision' => __('carrier_vessels.conflict')]);
        }

        return redirect()->route('admin.vessels.partner-requests.index')
            ->with('success', __('carrier_vessels.reviewed'));
    }
}
