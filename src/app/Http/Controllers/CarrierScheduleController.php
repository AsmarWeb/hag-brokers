<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\CarrierScheduleSubmission;
use App\Services\Freight\SailingCatalog;
use App\Services\Freight\SailingImport;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CarrierScheduleController extends Controller
{
    public function index(Request $request)
    {
        return $this->workspace($this->membership($request));
    }

    public function template(Request $request)
    {
        $this->membership($request);

        return response(implode(',', SailingImport::HEADERS)."\r\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="carrier-sailings.csv"',
        ]);
    }

    public function preview(Request $request, SailingImport $import, SailingCatalog $catalog)
    {
        $membership = $this->membership($request);
        $request->session()->forget('carrier_schedule_preview');
        $request->validate(['schedule' => ['required', 'file', 'extensions:csv', 'max:128']]);
        $rows = $import->read($request->file('schedule')->getRealPath());
        $plan = $this->plan($rows, $membership->carrier, $import, $catalog);
        $token = (string) Str::uuid();

        $request->session()->put('carrier_schedule_preview', [
            'membership' => $membership->id, 'token' => $token, 'rows' => $rows,
            'revision' => $import->revision($plan), 'expires' => now()->addMinutes(15)->timestamp,
        ]);

        return $this->workspace($membership, compact('plan', 'token'));
    }

    public function submit(Request $request, SailingImport $import, SailingCatalog $catalog)
    {
        $membership = $this->membership($request);
        $data = $request->validate(['token' => ['required', 'uuid']]);
        $preview = $request->session()->get('carrier_schedule_preview');
        if (! $preview || $preview['membership'] !== $membership->id
            || $preview['expires'] <= now()->timestamp || ! hash_equals($preview['token'], $data['token'])) {
            $this->stale();
        }

        DB::transaction(function () use ($request, $membership, $preview, $import, $catalog) {
            $carrier = Carrier::lockForUpdate()->findOrFail($membership->carrier_id);
            abort_unless($carrier->is_active && $request->user()->carrierPartnership()
                ->lockForUpdate()->first()?->id === $membership->id, 403);
            $plan = $this->plan($preview['rows'], $carrier, $import, $catalog, true);
            $revision = $import->revision($plan);
            if (! hash_equals($preview['revision'], $revision)) {
                $this->stale();
            }
            if (collect($plan)->every(fn ($row) => $row['action'] === 'unchanged')
                || CarrierScheduleSubmission::where('carrier_id', $carrier->id)->where('status', 'pending')
                    ->where('revision', $revision)->exists()) {
                throw ValidationException::withMessages(['schedule' => __('carrier_schedules.no_changes')]);
            }

            CarrierScheduleSubmission::create([
                'carrier_id' => $carrier->id, 'submitted_by' => $request->user()->id,
                'rows' => $preview['rows'], 'plan' => $plan, 'revision' => $revision,
            ]);
        }, 3);
        $request->session()->forget('carrier_schedule_preview');

        return redirect()->route('carrier.schedules.index')->with('success', __('carrier_schedules.submitted'));
    }

    public function queue(Request $request)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);

        return view('admin.freight-sailings.carrier-queue', [
            'submissions' => CarrierScheduleSubmission::where('status', 'pending')
                ->with('carrier')->oldest('id')->paginate(20),
        ]);
    }

    public function show(Request $request, CarrierScheduleSubmission $submission)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);

        return view('admin.freight-sailings.carrier-review', compact('submission'));
    }

    public function review(Request $request, CarrierScheduleSubmission $submission, SailingImport $import, SailingCatalog $catalog)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);
        $data = $request->validate([
            'decision' => ['required', 'in:publish,return'],
            'review_note' => ['required_if:decision,return', 'nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($request, $submission, $import, $catalog, $data) {
                $carrier = Carrier::lockForUpdate()->findOrFail($submission->carrier_id);
                $pending = CarrierScheduleSubmission::lockForUpdate()->findOrFail($submission->id);
                abort_unless($pending->status === 'pending', 409);

                if ($data['decision'] === 'publish') {
                    $plan = $this->plan($pending->rows, $carrier, $import, $catalog, true);
                    if (! hash_equals($pending->revision, $import->revision($plan))) {
                        $this->stale();
                    }
                    $import->apply($plan);
                }

                $pending->forceFill([
                    'status' => $data['decision'] === 'publish' ? 'published' : 'returned',
                    'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
                    'review_note' => $data['review_note'] ?? null,
                ])->save();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->stale();
        }

        return redirect()->route('admin.freight-sailings.carrier-queue')
            ->with('success', __('carrier_schedules.reviewed'));
    }

    private function membership(Request $request): CarrierInvitation
    {
        $membership = $request->user()->carrierPartnership()->with('carrier')->first();
        abort_unless($request->user()->hasVerifiedEmail() && $membership?->carrier?->is_active, 403);

        return $membership;
    }

    private function workspace(CarrierInvitation $membership, array $preview = [])
    {
        return response()->view('carrier-partner.schedules', $preview + [
            'carrier' => $membership->carrier,
            'submissions' => CarrierScheduleSubmission::where('carrier_id', $membership->carrier_id)->latest('id')->paginate(10),
        ])->header('Cache-Control', 'no-store');
    }

    private function plan(array $rows, Carrier $carrier, SailingImport $import, SailingCatalog $catalog, bool $lock = false): array
    {
        if (! $carrier->is_active || array_unique(array_column($rows, 'carrier_code')) !== [$carrier->code]) {
            throw ValidationException::withMessages(['schedule' => __('carrier_schedules.wrong_carrier')]);
        }

        return $import->plan($rows, $catalog, $lock);
    }

    private function stale(): never
    {
        throw ValidationException::withMessages(['schedule' => __('carrier_schedules.stale')]);
    }
}
