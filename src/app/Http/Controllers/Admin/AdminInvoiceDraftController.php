<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealCommission;
use App\Models\InvoiceDraft;
use App\Services\Revenue\InvoiceDraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminInvoiceDraftController extends Controller
{
    public function index(): View
    {
        return view('admin.invoice-drafts.index', [
            'drafts' => InvoiceDraft::with('deal')->latest()->orderByDesc('id')->paginate(20),
        ]);
    }

    public function store(Request $request, DealCommission $commission, InvoiceDraftService $service): RedirectResponse
    {
        $request->validate($this->protectedInputs());
        $draft = $service->create($commission, (int) $request->user()->id);
        return redirect()->route('admin.invoice-drafts.show', $draft)
            ->with('success', __('invoice_drafts.ready'));
    }

    public function show(InvoiceDraft $invoiceDraft, InvoiceDraftService $service): View
    {
        $invoiceDraft->load(['deal', 'agreement']);
        return view('admin.invoice-drafts.show', [
            'draft' => $invoiceDraft,
            'snapshotValid' => $service->hasValidSnapshot($invoiceDraft),
        ]);
    }

    public function update(Request $request, InvoiceDraft $invoiceDraft, InvoiceDraftService $service): RedirectResponse
    {
        $data = $request->validate($this->protectedInputs() + [
            'notes' => ['nullable', 'string', 'max:4000'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $service->updateMetadata($invoiceDraft, $data);
        return redirect()->route('admin.invoice-drafts.show', $invoiceDraft)
            ->with('success', __('invoice_drafts.saved'));
    }

    private function protectedInputs(): array
    {
        return array_fill_keys([
            'amount', 'currency', 'customer_id', 'deal_id', 'deal_commission_id',
            'deal_agreement_id', 'status', 'reference', 'snapshot', 'snapshot_sha256',
            'source_agreement_sha256', 'created_by',
        ], ['prohibited']);
    }
}
