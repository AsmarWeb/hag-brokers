<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreightRateBooking;
use App\Models\FreightRateBookingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminFreightRateBookingController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('q', ''));

        $query = FreightRateBooking::query()
            ->with(['quotation.originPort', 'quotation.destinationPort', 'customer', 'carrier'])
            ->when($status === 'attention', fn ($builder) => $builder->whereIn('status', ['admin_rejected', 'carrier_rejected', 'cancelled']))
            ->when($status !== '' && $status !== 'attention', fn ($builder) => $builder->where('status', $status))
            ->when($search !== '', function ($builder) use ($search): void {
                $term = '%'.mb_substr($search, 0, 100).'%';
                $id = trim($term, '%');
                $builder->where(function ($nested) use ($term, $id): void {
                    $nested->where('id', is_numeric($id) ? (int) $id : -1)
                        ->orWhere('carrier_reference', 'like', $term)
                        ->orWhereHas('quotation', fn ($quotation) => $quotation->where('reference_no', 'like', $term))
                        ->orWhereHas('customer', fn ($customer) => $customer->where('company_name', 'like', $term))
                        ->orWhereHas('carrier', fn ($carrier) => $carrier->where('name', 'like', $term));
                });
            });

        $bookings = $query->latest('id')->paginate(20)->withQueryString();

        $counts = [
            'all' => FreightRateBooking::count(),
            'request_pending' => FreightRateBooking::where('status', 'request_pending')->count(),
            'awaiting_carrier' => FreightRateBooking::where('status', 'awaiting_carrier')->count(),
            'confirmed' => FreightRateBooking::where('status', 'carrier_confirmed')->count(),
            'attention' => FreightRateBooking::whereIn('status', ['admin_rejected', 'carrier_rejected', 'cancelled'])->count(),
        ];

        return view('admin.freight-bookings.index', compact('bookings', 'counts', 'status', 'search'));
    }

    public function show(FreightRateBooking $booking)
    {
        $booking->load([
            'quotation.originPort',
            'quotation.destinationPort',
            'customer',
            'carrier',
            'events.actor',
        ]);

        return view('admin.freight-bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, FreightRateBooking $booking)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['mark_sent', 'reject', 'cancel'])],
            'note' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        DB::transaction(function () use ($request, $booking, $data): void {
            $locked = FreightRateBooking::query()->lockForUpdate()->findOrFail($booking->id);
            $from = $locked->status;

            if ($data['action'] === 'mark_sent') {
                if ($from !== 'request_pending') {
                    throw ValidationException::withMessages([
                        'action' => [__('freight_bookings.invalid_send_transition')],
                    ]);
                }

                $to = 'awaiting_carrier';
                $event = 'marked_sent_to_carrier';
            } elseif ($data['action'] === 'reject') {
                if ($from !== 'request_pending') {
                    throw ValidationException::withMessages([
                        'action' => [__('freight_bookings.invalid_reject_transition')],
                    ]);
                }

                $to = 'admin_rejected';
                $event = 'admin_rejected';
            } else {
                if (! in_array($from, ['request_pending', 'awaiting_carrier'], true)) {
                    throw ValidationException::withMessages([
                        'action' => [__('freight_bookings.invalid_cancel_transition')],
                    ]);
                }

                $to = 'cancelled';
                $event = 'cancelled_by_admin';
            }

            $locked->forceFill([
                'status' => $to,
                'status_note' => $data['note'],
            ])->save();

            $locked->events()->create([
                'actor_id' => $request->user()->id,
                'event_key' => $event,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $data['note'],
                'metadata' => ['source' => 'admin_web'],
            ]);
        }, 3);

        return redirect()
            ->route('admin.freight-bookings.show', $booking)
            ->with('success', __('freight_bookings.action_saved'));
    }
}
