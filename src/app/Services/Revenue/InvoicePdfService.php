<?php

namespace App\Services\Revenue;

use App\Models\IssuedInvoice;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

final class InvoicePdfService
{
    public function html(IssuedInvoice $invoice, string $language): string
    {
        abort_unless(in_array($language, ['ar', 'en'], true), 404);
        abort_unless(app(InvoiceIssuanceService::class)->hasValidSnapshot($invoice), 409, __('invoices.snapshot_invalid'));

        // No live customer, company, draft or commission data enters this document.
        return view('admin.invoices.pdf', [
            'document' => $invoice->snapshot,
            'language' => $language,
        ])->render();
    }

    public function output(IssuedInvoice $invoice, string $language): string
    {
        $html = $this->html($invoice, $language);
        abort_unless(class_exists(Mpdf::class), 503, 'Install the invoice PDF dependency using composer install.');
        $tempDir = storage_path('app/private/invoice-pdf-tmp');
        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            throw new \RuntimeException('Cannot create invoice PDF temporary directory.');
        }
        $pdf = new Mpdf([
            'mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'dejavusans',
            'margin_left' => 16, 'margin_right' => 16, 'margin_top' => 16, 'margin_bottom' => 20,
            'tempDir' => $tempDir, 'autoScriptToLang' => true, 'autoLangToFont' => true,
        ]);
        $pdf->SetDirectionality($language === 'ar' ? 'rtl' : 'ltr');
        $pdf->SetTitle('Invoice '.$invoice->snapshot['reference']);
        $pdf->SetAuthor('HAG Brokers');
        // All dynamic values in the dedicated template are escaped. No remote images,
        // stylesheets or editable document templates are consumed by the renderer.
        $pdf->SetHTMLFooter('<div style="text-align:center;font-size:8pt;color:#64748b">HAG Brokers | {PAGENO} / {nbpg}</div>');
        $pdf->WriteHTML($html);
        return $pdf->Output('', Destination::STRING_RETURN);
    }
}
