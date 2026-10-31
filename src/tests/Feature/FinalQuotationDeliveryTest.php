<?php

namespace Tests\Feature;

use App\Jobs\SendFinalQuotationMail;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\QuotationOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalQuotationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        config()->set('quotation_delivery.enabled', true);
        config()->set('mail.mailers.smtp.host', 'mailpit');
    }

    private function prepare(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $admin->addRole(Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        ));

        $customer = Customer::create(['company_name' => 'Delivery Test']);

        $recipient = User::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'delivery@example.test',
        ]);

        $origin = Port::create([
            'name' => 'Origin',
            'country_code' => 'AE',
        ]);

        $destination = Port::create([
            'name' => 'Destination',
            'country_code' => 'DE',
        ]);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'reference_no' => 'DEL-' . uniqid(),
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
        ]);

        $carrier = Carrier::create([
            'name' => 'Delivery Carrier',
            'code' => 'D-' . uniqid(),
            'is_active' => true,
        ]);

        $offer = QuotationOffer::create([
            'quotation_id' => $quotation->id,
            'carrier_id' => $carrier->id,
            'carrier_amount' => 1000,
            'customer_amount' => 1200,
            'currency' => 'USD',
            'status' => 'approved',
        ]);

        $quotation->forceFill([
            'approved_offer_id' => $offer->id,
            'approved_customer_amount' => '1200.00',
            'approved_currency' => 'USD',
        ])->save();

        $path = 'documents/generated/' . uniqid() . '.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test content');

        $document = Document::create([
            'name' => 'Final quotation',
            'type' => 'final_quotation',
            'status' => 'active',
            'documentable_type' => 'quotation',
            'documentable_id' => $quotation->id,
            'file_path' => $path,
            'original_name' => 'Quotation.pdf',
            'uploaded_by' => $admin->id,
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => $path,
            'original_name' => 'Quotation.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => strlen('%PDF-1.4 test content'),
            'uploaded_by' => $admin->id,
        ]);

        return [$admin, $recipient, $quotation, $document];
    }

    private function sendUrl(Quotation $quotation, Document $document): string
    {
        return route('admin.quotations.final-pdf.send', [
            $quotation, $document,
        ]);
    }

    public function test_admin_queues_delivery_once(): void
    {
        Queue::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), [
                'recipient_user_id' => $recipient->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('quotation_deliveries', [
            'document_id' => $document->id,
            'status' => 'queued',
            'recipient_email' => $recipient->email,
        ]);

        Queue::assertPushed(SendFinalQuotationMail::class, 1);
    }

    public function test_duplicate_request_is_blocked(): void
    {
        Queue::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $payload = ['recipient_user_id' => $recipient->id];

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), $payload)
            ->assertSessionHasErrors('delivery');

        $this->assertDatabaseCount('quotation_deliveries', 1);
        Queue::assertPushed(SendFinalQuotationMail::class, 1);
    }

    public function test_delivery_requires_approved_document(): void
    {
        Queue::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $document->update(['status' => 'draft']);

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), [
                'recipient_user_id' => $recipient->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('quotation_deliveries', 0);
    }

    public function test_customer_cannot_queue_delivery(): void
    {
        Queue::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $this->actingAs($recipient)
            ->post($this->sendUrl($quotation, $document), [
                'recipient_user_id' => $recipient->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('quotation_deliveries', 0);
    }

    public function test_worker_marks_mail_sent_after_transport_acceptance(): void
    {
        Queue::fake();
        Mail::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), [
                'recipient_user_id' => $recipient->id,
            ])
            ->assertSessionHasNoErrors();

        $id = DB::table('quotation_deliveries')->value('id');

        (new SendFinalQuotationMail($id))->handle();

        $this->assertDatabaseHas('quotation_deliveries', [
            'id' => $id,
            'status' => 'sent',
        ]);

        Mail::assertSent(\App\Mail\FinalQuotationMail::class, 1);
    }

    public function test_tampered_pdf_fails_integrity_check(): void
    {
        Queue::fake();
        Mail::fake();

        [$admin, $recipient, $quotation, $document] = $this->prepare();

        $this->actingAs($admin)
            ->post($this->sendUrl($quotation, $document), [
                'recipient_user_id' => $recipient->id,
            ])
            ->assertSessionHasNoErrors();

        Storage::disk('local')->put(
            $document->file_path,
            '%PDF-1.4 modified document'
        );

        $id = DB::table('quotation_deliveries')->value('id');

        (new SendFinalQuotationMail($id))->handle();

        $this->assertDatabaseHas('quotation_deliveries', [
            'id' => $id,
            'status' => 'failed',
        ]);

        Mail::assertNothingSent();
    }
}
