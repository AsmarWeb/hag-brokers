<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceDraft;
use App\Models\IssuedInvoice;
use App\Services\Revenue\InvoiceIssuanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

final class AdminInvoiceController extends Controller
{
    public function index(InvoiceIssuanceService $service): View
    {
        $invoices = IssuedInvoice::orderByDesc('id')->paginate(20);
        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'invoiceValidity' => $invoices->getCollection()->mapWithKeys(fn ($invoice) => [$invoice->id => $service->hasValidSnapshot($invoice)]),
        ]);
    }

    public function review(InvoiceDraft $invoiceDraft, InvoiceIssuanceService $service): View|RedirectResponse
    {
        if ($invoiceDraft->issuedInvoice) return redirect()->route('admin.invoices.show', $invoiceDraft->issuedInvoice);
        $issuer = $service->issuer();
        return view('admin.invoices.review', [
            'draft' => $invoiceDraft, 'issuer' => $issuer,
            'issuerReady' => $service->issuerReady($issuer), 'sourceReady' => $service->sourceReady($invoiceDraft),
        ]);
    }

    public function preview(Request $request, InvoiceDraft $invoiceDraft, InvoiceIssuanceService $service): View|RedirectResponse
    {
        if ($invoiceDraft->issuedInvoice) return redirect()->route('admin.invoices.show', $invoiceDraft->issuedInvoice);
        try {
            $review = $service->preview($invoiceDraft, $request->all(), (int) $request->user()->id);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) throw $exception;
            return redirect()->route('admin.invoices.review', $invoiceDraft)
                ->withErrors($exception->errors())->withInput($request->except('_token', 'review_token', 'confirm'));
        }
        return view('admin.invoices.preview', ['draft' => $invoiceDraft] + $review);
    }

    public function issue(Request $request, InvoiceDraft $invoiceDraft, InvoiceIssuanceService $service): RedirectResponse
    {
        try {
            $data = $request->validate([
                'review_token' => ['required', 'string', 'max:100000'],
                'confirm' => ['accepted'],
            ]);
            $invoice = $service->issue($invoiceDraft, $data['review_token'], (int) $request->user()->id);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) throw $exception;
            // Return to a GET route, never redirect to the POST-only preview URL.
            return redirect()->route('admin.invoices.review', $invoiceDraft)->withErrors($exception->errors());
        }
        return redirect()->route('admin.invoices.show', $invoice)->with('success', __('invoices.issued_success'));
    }

    public function show(IssuedInvoice $invoice, InvoiceIssuanceService $service): View
    {
        return view('admin.invoices.show', ['invoice' => $invoice, 'snapshotValid' => $service->hasValidSnapshot($invoice)]);
    }
}
