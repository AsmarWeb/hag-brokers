<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalQuotationPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function admin(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        return $user;
    }

    private function quotation(bool $approved): Quotation
    {
        $customer = Customer::create([
            'company_name' => 'PDF Test Customer',
            'country_code' => 'AE',
        ]);

        $origin = Port::create([
            'name' => 'Jebel Ali',
            'country_code' => 'AE',
        ]);

        $destination = Port::create([
            'name' => 'Hamburg',
            'country_code' => 'DE',
        ]);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
            'reference_no' => 'PDF-' . uniqid(),
        ]);

        if ($approved) {
            // This test exercises PDF generation from an existing
            // approval snapshot; no fresh approval operation is needed.
            $quotation->forceFill([
                'approved_offer_id' => null,
                'approved_carrier_amount' => '1000.00',
                'approved_customer_amount' => '1300.00',
                'approved_margin' => '300.00',
                'approved_currency' => 'USD',
            ])->save();

            // Create a valid approved carrier offer and link the snapshot.
            $carrier = \App\Models\Carrier::create([
                'name' => 'PDF Carrier',
                'code' => 'PDF-' . uniqid(),
                'is_active' => true,
            ]);

            $offer = \App\Models\QuotationOffer::create([
                'quotation_id' => $quotation->id,
                'carrier_id' => $carrier->id,
                'carrier_amount' => '1000.00',
                'customer_amount' => '1300.00',
                'currency' => 'USD',
                'status' => 'approved',
            ]);

            $quotation->forceFill([
                'approved_offer_id' => $offer->id,
            ])->save();
        }

        return $quotation;
    }

    private function template(): void
    {
        DocumentTemplate::create([
            'name' => 'Quotation',
            'slug' => 'quotation',
            'entity_type' => 'quotation',
            'document_type' => 'quotation',
            'content' => '<h1>Quotation {{quotation.reference_no}}</h1>'
                . '<p>Price {{quotation.total_charge}}</p>'
                . '<p>{{customer.company_name}}</p>',
            'is_active' => true,
        ]);
    }

    public function test_admin_generates_pdf_and_document_version(): void
    {
        $this->template();
        $quotation = $this->quotation(true);

        $this->actingAs($this->admin())
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $document = Document::firstOrFail();

        $this->assertSame('final_quotation', $document->type);
        $this->assertSame('draft', $document->status);
        $this->assertSame('quotation', $document->documentable_type);
        $this->assertEquals($quotation->id, $document->documentable_id);
        $this->assertSame(1, $document->versions()->count());

        Storage::disk('local')->assertExists($document->file_path);
        $this->assertStringStartsWith(
            '%PDF-',
            Storage::disk('local')->get($document->file_path)
        );

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'event' => 'final_pdf_generated',
        ]);
    }

    public function test_unapproved_quotation_cannot_generate_pdf(): void
    {
        $this->template();
        $quotation = $this->quotation(false);

        $this->actingAs($this->admin())
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasErrors('pdf');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_duplicate_pdf_is_not_created(): void
    {
        $this->template();
        $quotation = $this->quotation(true);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_versions', 1);
    }

    public function test_customer_cannot_generate_final_pdf(): void
    {
        $this->template();
        $quotation = $this->quotation(true);

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_admin_approves_draft_pdf_without_sending(): void
    {
        $this->template();
        $quotation = $this->quotation(true);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $document = Document::firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.final-pdf.approve', [
                $quotation, $document,
            ]))
            ->assertRedirect(route('admin.documents.versions', $document))
            ->assertSessionHasNoErrors();

        $this->assertSame('active', $document->fresh()->status);

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'user_id' => $admin->id,
            'event' => 'final_pdf_approved',
        ]);
    }

    public function test_cannot_approve_pdf_twice(): void
    {
        $this->template();
        $quotation = $this->quotation(true);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $document = Document::firstOrFail();
        $url = route('admin.quotations.final-pdf.approve', [
            $quotation, $document,
        ]);

        $this->actingAs($admin)->patch($url)->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch($url)->assertSessionHasErrors('approval');

        $this->assertSame(
            1,
            \Illuminate\Support\Facades\DB::table('quotation_history')
                ->where('event', 'final_pdf_approved')
                ->count()
        );
    }

    public function test_customer_cannot_approve_pdf(): void
    {
        $this->template();
        $quotation = $this->quotation(true);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $document = Document::firstOrFail();

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->patch(route('admin.quotations.final-pdf.approve', [
                $quotation, $document,
            ]))
            ->assertForbidden();

        $this->assertSame('draft', $document->fresh()->status);
    }

    public function test_missing_pdf_cannot_be_approved(): void
    {
        $this->template();
        $quotation = $this->quotation(true);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.final-pdf.store', $quotation))
            ->assertSessionHasNoErrors();

        $document = Document::firstOrFail();
        Storage::disk('local')->delete($document->file_path);

        $this->actingAs($admin)
            ->patch(route('admin.quotations.final-pdf.approve', [
                $quotation, $document,
            ]))
            ->assertSessionHasErrors('approval');

        $this->assertSame('draft', $document->fresh()->status);
    }

}
