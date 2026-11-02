<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\IssuedInvoice;
use App\Services\Revenue\InvoicePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AdminInvoicePaymentController extends Controller
{
    public function show(IssuedInvoice $invoice, InvoicePaymentService $service): View
    {
        try { $summary = $service->summary($invoice); $integrityError = null; }
        catch (ValidationException $exception) { $summary = null; $integrityError = collect($exception->errors())->flatten()->first(); }
        return view('admin.invoices.payments', [
            'invoice' => $invoice, 'summary' => $summary, 'integrityError' => $integrityError,
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, IssuedInvoice $invoice, InvoicePaymentService $service): RedirectResponse
    {
        $service->record($invoice, $request->all(), (int) $request->user()->id);
        return redirect()->route('admin.invoices.payments', $invoice)->with('success', __('payments.recorded'));
    }

    public function reverse(Request $request, IssuedInvoice $invoice, InvoicePayment $payment, InvoicePaymentService $service): RedirectResponse
    {
        abort_unless((int) $payment->issued_invoice_id === (int) $invoice->id, 404);
        $service->reverse($invoice, $payment, $request->all(), (int) $request->user()->id);
        return redirect()->route('admin.invoices.payments', $invoice)->with('success', __('payments.reversed_success'));
    }
}
