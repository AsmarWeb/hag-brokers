<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminFinalQuotationApprovalController extends Controller
{
    public function approve(
        Request $request,
        Quotation $quotation,
        Document $document
    ): RedirectResponse {
        DB::transaction(function () use ($request, $quotation, $document) {
            $lockedQuotation = Quotation::query()
                ->whereKey($quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedQuotation->approved_offer_id === null) {
                throw ValidationException::withMessages([
                    'approval' => 'The carrier offer must be approved first.',
                ]);
            }

            $lockedDocument = Document::query()
                ->whereKey($document->id)
                ->where('documentable_type', 'quotation')
                ->where('documentable_id', $lockedQuotation->id)
                ->where('type', 'final_quotation')
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDocument->status !== 'draft') {
                throw ValidationException::withMessages([
                    'approval' => 'Only draft documents can be approved.',
                ]);
            }

            $version = $lockedDocument->versions()
                ->orderByDesc('version')
                ->first();

            if (
                ! $version
                || ! is_string($version->file_path)
                || ! preg_match(
                    '~\Adocuments/[A-Za-z0-9/_-]+\.pdf\z~D',
                    $version->file_path
                )
                || ! Storage::disk('local')->exists($version->file_path)
            ) {
                throw ValidationException::withMessages([
                    'approval' => 'The PDF file is missing or invalid.',
                ]);
            }

            $lockedDocument->update(['status' => 'active']);

            DB::table('quotation_history')->insert([
                'quotation_id' => $lockedQuotation->id,
                'user_id' => $request->user()->id,
                'event' => 'final_pdf_approved',
                'old_value' => 'draft',
                'new_value' => (string) $lockedDocument->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);

        return redirect()
            ->route('admin.documents.versions', $document)
            ->with(
                'success',
                'Final quotation PDF approved for publishing. No email was sent.'
            );
    }
}
