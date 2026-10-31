<?php

namespace App\Jobs;

use App\Mail\FinalQuotationMail;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SendFinalQuotationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // A failed SMTP attempt requires explicit review.
    // Automatic retries may accidentally send duplicate emails.
    public int $tries = 1;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = DB::table('quotation_deliveries')
            ->where('id', $this->deliveryId)
            ->first();

        if (! $delivery || $delivery->status !== 'queued') {
            return;
        }

        try {
            if (
                ! app()->environment(['local', 'testing'])
                || ! config('quotation_delivery.enabled', false)
                || config('mail.mailers.smtp.host') !== 'mailpit'
            ) {
                throw new RuntimeException('Local Mailpit delivery is disabled.');
            }

            $document = Document::query()
                ->whereKey($delivery->document_id)
                ->where('documentable_type', 'quotation')
                ->where('documentable_id', $delivery->quotation_id)
                ->where('status', 'active')
                ->where('type', 'final_quotation')
                ->firstOrFail();

            $version = DocumentVersion::query()
                ->whereKey($delivery->document_version_id)
                ->where('document_id', $document->id)
                ->firstOrFail();

            $path = $version->file_path;

            if (
                ! is_string($path)
                || ! preg_match('~\Adocuments/[A-Za-z0-9/_-]+\.pdf\z~D', $path)
                || ! Storage::disk('local')->exists($path)
            ) {
                throw new RuntimeException('PDF attachment is missing.');
            }

            $pdf = Storage::disk('local')->get($path);

            if (
                ! is_string($pdf)
                || ! str_starts_with($pdf, '%PDF-')
                || ! hash_equals($delivery->file_sha256, hash('sha256', $pdf))
            ) {
                throw new RuntimeException('PDF integrity check failed.');
            }

            Mail::mailer('smtp')
                ->to($delivery->recipient_email)
                ->send(new FinalQuotationMail(
                    (string) $delivery->quotation_id,
                    $pdf
                ));

            DB::table('quotation_deliveries')
                ->where('id', $delivery->id)
                ->where('status', 'queued')
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            DB::table('quotation_deliveries')
                ->where('id', $delivery->id)
                ->where('status', 'queued')
                ->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'last_error' => mb_substr($exception->getMessage(), 0, 1000),
                    'updated_at' => now(),
                ]);

            report($exception);
        }
    }
}
