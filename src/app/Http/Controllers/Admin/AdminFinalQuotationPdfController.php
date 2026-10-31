<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Quotation;
use App\Services\DocumentPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminFinalQuotationPdfController extends Controller
{
    public function store(
        Request $request,
        Quotation $quotation,
        DocumentPdfService $pdfService
    ): RedirectResponse {
        $quotation->load([
            'customer',
            'originPort',
            'destinationPort',
        ]);

        if (
            $quotation->approved_offer_id === null
            || $quotation->approved_customer_amount === null
            || $quotation->approved_currency === null
        ) {
            throw ValidationException::withMessages([
                'pdf' => 'Approve a carrier offer before generating the PDF.',
            ]);
        }

        $template = DocumentTemplate::query()
            ->where('slug', 'quotation')
            ->where('entity_type', 'quotation')
            ->where('document_type', 'quotation')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            throw ValidationException::withMessages([
                'pdf' => 'An active quotation document template is required.',
            ]);
        }

        $existing = $this->existingDocument($quotation);

        if ($existing) {
            return redirect()
                ->route('admin.documents.versions', $existing)
                ->with('success', 'The final quotation PDF already exists.');
        }

        // Expose customer-facing pricing only.
        // Never pass internal carrier cost or brokerage margin.
        $quotationData = [
            'reference_no' => $quotation->reference_no,
            'created_at' => $quotation->created_at?->format('Y-m-d'),
            'status' => 'Prepared',
            'origin_port' => $quotation->originPort?->name,
            'destination_port' => $quotation->destinationPort?->name,
            'cargo_type' => $quotation->cargo_type,
            'container_type' => $quotation->container_type,
            'quantity' => $quotation->quantity,
            'weight' => $quotation->weight,
            'total_charge' => $quotation->approved_currency . ' '
                . $quotation->approved_customer_amount,
        ];

        // TemplateRenderer inserts values into HTML without escaping.
        // Escape user-derived fields before handing them to the renderer.
        $quotationData = array_map(
            static fn ($value) => htmlspecialchars(
                (string) ($value ?? ''),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ),
            $quotationData
        );

        $data = [
            'quotation' => $quotationData,
            'customer' => [
                'company_name' => htmlspecialchars(
                    (string) ($quotation->customer?->company_name ?? ''),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ),
            ],
        ];

        $pdf = $pdfService->output($template, $data);

        $path = 'documents/generated/' . Str::uuid() . '.pdf';

        if (Storage::disk('local')->put($path, $pdf) !== true) {
            throw new \RuntimeException('Unable to save final quotation PDF.');
        }

        try {
            $document = DB::transaction(function () use (
                $request,
                $quotation,
                $path,
                $pdf
            ) {
                $locked = Quotation::query()
                    ->whereKey($quotation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->approved_offer_id === null) {
                    throw ValidationException::withMessages([
                        'pdf' => 'The quotation no longer has an approved offer.',
                    ]);
                }

                if ($this->existingDocument($locked)) {
                    throw ValidationException::withMessages([
                        'pdf' => 'The final quotation PDF already exists.',
                    ]);
                }

                $filename = 'HAG-Quotation-' . $locked->id . '.pdf';

                $document = Document::query()->create([
                    'name' => 'Final Quotation - ' . $locked->reference_no,
                    'type' => 'final_quotation',
                    'status' => 'draft',
                    'documentable_type' => 'quotation',
                    'documentable_id' => $locked->id,
                    'shipment_id' => null,
                    'file_path' => $path,
                    'original_name' => $filename,
                    'uploaded_by' => $request->user()->id,
                ]);

                $document->versions()->create([
                    'version' => 1,
                    'file_path' => $path,
                    'original_name' => $filename,
                    'mime_type' => 'application/pdf',
                    'file_size' => strlen($pdf),
                    'uploaded_by' => $request->user()->id,
                ]);

                DB::table('quotation_history')->insert([
                    'quotation_id' => $locked->id,
                    'user_id' => $request->user()->id,
                    'event' => 'final_pdf_generated',
                    'old_value' => null,
                    'new_value' => (string) $document->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $document;
            }, 3);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('admin.documents.versions', $document)
            ->with('success', 'Final quotation PDF saved as a draft.');
    }

    private function existingDocument(Quotation $quotation): ?Document
    {
        return Document::query()
            ->where('documentable_type', 'quotation')
            ->where('documentable_id', $quotation->id)
            ->where('type', 'final_quotation')
            ->first();
    }
}
