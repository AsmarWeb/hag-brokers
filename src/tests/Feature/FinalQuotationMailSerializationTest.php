<?php

namespace Tests\Feature;

use App\Mail\FinalQuotationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FinalQuotationMailSerializationTest extends TestCase
{
    public function test_binary_pdf_can_be_encoded_as_json_queue_payload(): void
    {
        $pdf = "%PDF-1.4\n" . random_bytes(128);

        $mailable = new FinalQuotationMail('TEST-001', $pdf);

        $serialized = serialize($mailable);

        $payload = json_encode([
            'command' => $serialized,
        ], JSON_THROW_ON_ERROR);

        $this->assertNotEmpty($payload);
        $this->assertStringNotContainsString($pdf, $payload);

        $restored = unserialize(
            json_decode($payload, true, 512, JSON_THROW_ON_ERROR)['command']
        );

        $this->assertSame(
            $pdf,
            base64_decode($restored->pdfBase64, true)
        );
    }

    public function test_binary_pdf_can_be_queued(): void
    {
        Mail::fake();

        $pdf = "%PDF-1.4\n" . random_bytes(128);

        Mail::to('hag-test@example.test')
            ->queue(new FinalQuotationMail('TEST-002', $pdf));

        Mail::assertQueued(FinalQuotationMail::class, 1);
    }
}
