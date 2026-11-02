<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IssuedInvoice;
use App\Services\Revenue\InvoicePdfService;
use Symfony\Component\HttpFoundation\Response;

final class AdminInvoicePdfController extends Controller
{
    public function download(IssuedInvoice $invoice, string $language, InvoicePdfService $service): Response
    {
        $bytes = $service->output($invoice, $language);
        $reference = preg_replace('/[^A-Za-z0-9_-]/', '-', $invoice->snapshot['reference']);
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$reference.'-'.$language.'.pdf"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
