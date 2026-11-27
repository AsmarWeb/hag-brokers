<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealCommission;
use App\Models\DealDocument;
use App\Models\FreightRateBooking;
use App\Models\FreightBookingOffer;
use App\Models\FreightSailing;
use App\Models\Shipment;
use App\Services\Revenue\DealExecutionHandoffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminDealExecutionController extends Controller
{
    public function index(): View
    {
        $eligibleDocuments = DealDocument::query()
            ->with(['deal.customer', 'deal.quotation.originPort', 'deal.quotation.destinationPort'])
            ->where('type', 'fixture_recap')
            ->where('status', 'signed')
            ->whereHas('deal', fn ($query) => $query
                ->where('status', 'approved')
                ->whereNotNull('quotation_id'))
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('shipments')
                    ->whereColumn('shipments.deal_document_id', 'deal_documents.id');
            })
            ->latest('signed_at')
            ->paginate(15, ['*'], 'recaps_page');

        $executions = Shipment::query()
            ->with(['customer', 'quotation.originPort', 'quotation.destinationPort', 'carrier', 'vessel'])
            ->whereNotNull('deal_document_id')
            ->latest()
            ->paginate(15, ['*'], 'executions_page');

        return view('admin.deal-execution.index', [
            'eligibleDocuments' => $eligibleDocuments,
            'executions' => $executions,
        ]);
    }

    public function create(
        DealDocument $dealDocument,
        DealExecutionHandoffService $service
    ): View|RedirectResponse {
        $existing = Shipment::query()
            ->where('deal_document_id', $dealDocument->getKey())
            ->first();

        if ($existing) {
            return redirect()->route('admin.deal-execution.show', $existing);
        }

        $context = $service->context($dealDocument);

        return view('admin.deal-execution.create', $context + [
            'sailings' => $service->availableSailings($dealDocument, $context),
            'confirmedBookings' => $service->confirmedRateBookings($dealDocument, $context),
            'selectedSailingId' => $context['quotation']->freight_sailing_id,
        ]);
    }

    public function store(
        Request $request,
        DealDocument $dealDocument,
        DealExecutionHandoffService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'freight_rate_booking_id' => ['nullable', 'integer', 'exists:freight_rate_bookings,id'],
            'freight_sailing_id' => ['nullable', 'integer', 'exists:freight_sailings,id'],
        ]);

        $shipment = $service->createShipment(
            $dealDocument,
            $validated,
            (int) $request->user()->getAuthIdentifier()
        );

        return redirect()
            ->route('admin.deal-execution.show', $shipment)
            ->with('success', __('deal_execution.created'));
    }

    public function show(
        Shipment $shipment,
        DealExecutionHandoffService $service
    ): View {
        abort_unless($shipment->deal_document_id !== null && $shipment->deal_id !== null, 404);

        $shipment->load([
            'customer', 'quotation.originPort', 'quotation.destinationPort',
            'originPort', 'destinationPort', 'carrier', 'vessel', 'events',
        ]);

        $document = DealDocument::query()
            ->with(['parties', 'deal'])
            ->findOrFail($shipment->deal_document_id);
        $deal = Deal::query()
            ->with(['customer', 'quotation.originPort', 'quotation.destinationPort', 'agreements', 'commissions.invoiceDraft.issuedInvoice'])
            ->findOrFail($shipment->deal_id);
        $booking = $shipment->freight_rate_booking_id
            ? FreightRateBooking::query()->with('carrier')->find($shipment->freight_rate_booking_id)
            : null;
        $legacyBooking = $shipment->freight_booking_offer_id
            ? FreightBookingOffer::query()->find($shipment->freight_booking_offer_id)
            : null;
        $sailing = $shipment->freight_sailing_id
            ? FreightSailing::query()->with(['carrier', 'vessel', 'originPort', 'destinationPort'])->find($shipment->freight_sailing_id)
            : null;
        $commission = $deal->commissions->first();
        $invoiceDraft = $commission?->invoiceDraft;
        $sourceIntegrity = $service->verifyShipmentSnapshot($shipment, $document);

        return view('admin.deal-execution.show', [
            'shipment' => $shipment,
            'document' => $document,
            'deal' => $deal,
            'booking' => $booking,
            'legacyBooking' => $legacyBooking,
            'sailing' => $sailing,
            'commission' => $commission,
            'invoiceDraft' => $invoiceDraft,
            'sourceIntegrity' => $sourceIntegrity,
        ]);
    }
}
