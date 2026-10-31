<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendFinalQuotationMail;
use App\Models\Document;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminQuotationDeliveryController extends Controller
{
    public function store(
        Request $request,
        Quotation $quotation,
        Document $document
    ): RedirectResponse {
        if (
            ! app()->environment(['local', 'testing'])
            || ! config('quotation_delivery.enabled', false)
            || config('mail.mailers.smtp.host') !== 'mailpit'
        ) {
            throw ValidationException::withMessages([
                'delivery' => 'Email delivery is disabled. Configure local Mailpit first.',
            ]);
        }

        $data = $request->validate([
            'recipient_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $recipient = User::query()
            ->whereKey($data['recipient_user_id'])
            ->where('customer_id', $quotation->customer_id)
            ->first();

        if (
            ! $recipient
            || ! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)
        ) {
            throw ValidationException::withMessages([
                'recipient_user_id' => 'Select a valid user belonging to this customer.',
            ]);
        }

        $deliveryId = DB::transaction(function () use (
            $quotation,
            $document,
            $recipient,
            $request
        ) {
            $locked = Quotation::query()
                ->whereKey($quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->approved_offer_id === null) {
                throw ValidationException::withMessages([
                    'delivery' => 'Approve a carrier offer first.',
                ]);
            }

            $approvedDocument = Document::query()
                ->whereKey($document->id)
                ->where('documentable_type', 'quotation')
                ->where('documentable_id', $locked->id)
                ->where('type', 'final_quotation')
                ->where('status', 'active')
                ->firstOrFail();

            if (
                DB::table('quotation_deliveries')
                    ->where('document_id', $approvedDocument->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'delivery' => 'A delivery request already exists for this document.',
                ]);
            }

            $version = $approvedDocument->versions()
                ->orderByDesc('version')
                ->firstOrFail();

            $path = $version->file_path;

            if (
                ! is_string($path)
                || ! preg_match('~\Adocuments/[A-Za-z0-9/_-]+\.pdf\z~D', $path)
                || ! Storage::disk('local')->exists($path)
            ) {
                throw ValidationException::withMessages([
                    'delivery' => 'The approved PDF is missing.',
                ]);
            }

            $bytes = Storage::disk('local')->get($path);

            if (! is_string($bytes) || ! str_starts_with($bytes, '%PDF-')) {
                throw ValidationException::withMessages([
                    'delivery' => 'Invalid PDF content.',
                ]);
            }

            $id = DB::table('quotation_deliveries')->insertGetId([
                'quotation_id' => $locked->id,
                'document_id' => $approvedDocument->id,
                'document_version_id' => $version->id,
                'file_sha256' => hash('sha256', $bytes),
                'recipient_email' => $recipient->email,
                'requested_by' => $request->user()->id,
                'status' => 'queued',
                'queued_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('quotation_history')->insert([
                'quotation_id' => $locked->id,
                'user_id' => $request->user()->id,
                'event' => 'final_pdf_email_queued',
                'old_value' => null,
                'new_value' => (string) $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SendFinalQuotationMail::dispatch($id)->afterCommit();

            return $id;
        }, 3);

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'Email request #' . $deliveryId . ' queued for Mailpit.');
    }
}
