<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\FreightRateBooking;
use App\Models\FreightBookingOffer;
use App\Models\QuotationOffer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Deal;
use App\Models\DealDocument;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealApprovalService;
use App\Services\Revenue\DealExecutionHandoffService;
use App\Services\Revenue\DealDocumentWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealExecutionHandoffTest extends TestCase
{
    use RefreshDatabase;

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

    private function deal(): Deal
    {
        $customer = Customer::create([
            'company_name' => 'HAG Deal Execution Test LLC',
            'country_code' => 'AE',
        ]);

        return Deal::create([
            'reference' => 'HAG-EXEC-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'collection_model' => 'commission_only',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => null,
            'platform_revenue' => '0.00',
        ]);
    }

    private function fixtureRecapDraft(Deal $deal, User $admin): DealDocument
    {
        return app(DealDocumentWorkflowService::class)->createDraft($deal, [
            'type' => 'fixture_recap',
            'title' => 'Fixture Recap – Handoff Test',
            'language' => 'en',
            'parties' => [
                [
                    'role_key' => 'party_a',
                    'company_name' => 'Party A Trading LLC',
                    'contact_name' => 'Signatory A',
                    'email' => 'party-a@example.test',
                ],
                [
                    'role_key' => 'party_b',
                    'company_name' => 'Party B Shipping Ltd',
                    'contact_name' => 'Signatory B',
                    'email' => 'party-b@example.test',
                ],
            ],
            'content' => [
                'commodity' => 'Steel coils',
                'quantity' => '2500',
                'quantity_unit' => 'MT',
                'loading_port' => 'Jebel Ali, UAE',
                'discharge_port' => 'Mombasa, Kenya',
                'laycan_from' => '2026-12-10',
                'laycan_to' => '2026-12-15',
                'vessel_name' => 'MV Test Carrier',
                'freight_rate' => '42.50',
                'freight_currency' => 'USD',
                'freight_rate_basis' => 'per metric ton',
                'payment_terms' => 'Payment against agreed shipping documents.',
                'demurrage_rate' => '12000',
                'despatch_rate' => '6000',
                'additional_terms' => 'Subject to operational confirmation.',
            ],
        ], (int) $admin->id);
    }

    /**
     * Build a complete approved deal and a fully signed Fixture Recap using
     * the same approval and evidence services used by the application.
     *
     * @return array{0: DealDocument, 1: Quotation}
     */
    private function signedFixtureRecap(User $admin, string $loadingPort = 'Jebel Ali, UAE'): array
    {
        Storage::fake('local');

        $customer = Customer::create([
            'company_name' => 'HAG Execution Test Customer LLC',
            'contact_person' => 'Operations Contact',
            'country_code' => 'AE',
        ]);

        $origin = Port::create([
            'name' => 'Jebel Ali Port',
            'country' => 'United Arab Emirates',
            'country_code' => 'AE',
            'port_code' => 'JEA',
            'unlocode' => 'AEJEA',
            'timezone' => 'Asia/Dubai',
            'is_active' => true,
        ]);
        $destination = Port::create([
            'name' => 'Khalifa Port',
            'country' => 'United Arab Emirates',
            'country_code' => 'AE',
            'port_code' => 'KHL',
            'unlocode' => 'AEKHL',
            'timezone' => 'Asia/Dubai',
            'is_active' => true,
        ]);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'Steel coils',
            'container_type' => '40ft',
            'quantity' => 2,
            'weight' => '2500.00',
            'preferred_date' => '2026-12-10',
            'currency' => 'AED',
            'total_charge' => '12500.00',
            'status' => 'approved',
            'reference_no' => 'HAG-QUOTE-' . Str::upper(Str::random(8)),
        ]);

        $deal = Deal::create([
            'reference' => 'HAG-EXEC-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'collection_model' => 'commission_only',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '12500.00',
            'supplier_cost' => null,
            'platform_revenue' => '0.00',
        ]);

        $agreement = $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => 'percent',
            'commission_bps' => 250,
            'fixed_fee' => null,
            'terms' => 'Approved brokerage commission terms for the execution handoff test.',
        ]);
        app(DealApprovalService::class)->approve($deal, $agreement, (int) $admin->id);

        $document = app(DealDocumentWorkflowService::class)->createDraft($deal->fresh(), [
            'type' => 'fixture_recap',
            'title' => 'Fixture Recap – Execution Test',
            'language' => 'en',
            'parties' => [
                [
                    'role_key' => 'party_a',
                    'company_name' => 'Party A Trading LLC',
                    'contact_name' => 'Signatory A',
                    'email' => 'party-a@example.test',
                ],
                [
                    'role_key' => 'party_b',
                    'company_name' => 'Party B Shipping Ltd',
                    'contact_name' => 'Signatory B',
                    'email' => 'party-b@example.test',
                ],
            ],
            'content' => [
                'commodity' => 'Steel coils',
                'quantity' => '2500',
                'quantity_unit' => 'MT',
                'loading_port' => $loadingPort,
                'discharge_port' => 'Khalifa Port, UAE',
                'laycan_from' => '2026-12-10',
                'laycan_to' => '2026-12-15',
                'vessel_name' => 'MV Execution Test',
                'freight_rate' => '42.50',
                'freight_currency' => 'USD',
                'freight_rate_basis' => 'per metric ton',
                'payment_terms' => 'Payment against agreed shipping documents.',
                'demurrage_rate' => '12000',
                'despatch_rate' => '6000',
                'additional_terms' => 'Subject to operational confirmation.',
            ],
        ], (int) $admin->id);

        $workflow = app(DealDocumentWorkflowService::class);
        $document = $workflow->prepareForSignature($document, (int) $admin->id);

        foreach ($document->parties()->orderBy('signing_order')->get() as $index => $party) {
            $document = $workflow->recordSignature(
                $document,
                $party,
                UploadedFile::fake()->create('signed-party-' . $index . '.pdf', 12, 'application/pdf'),
                [
                    'signed_by_name' => 'Authorized Signatory ' . ($index + 1),
                    'signed_by_title' => 'Director',
                    'signed_at' => now()->toDateString(),
                ],
                (int) $admin->id
            );
        }

        return [$document->fresh(), $quotation->fresh()];
    }

    public function test_guest_cannot_access_deal_execution_center(): void
    {
        $this->get(route('admin.deal-execution.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_deal_execution_center(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('admin.deal-execution.index'))
            ->assertForbidden();
    }

    public function test_draft_fixture_recap_cannot_be_handed_off_to_shipment_execution(): void
    {
        $admin = $this->admin();
        $document = $this->fixtureRecapDraft($this->deal(), $admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertSessionHasErrors('execution');

        $this->assertDatabaseCount('shipments', 0);
        $this->assertSame('draft', $document->fresh()->status);
    }

    public function test_fully_signed_fixture_recap_creates_pending_shipment_and_audit_records(): void
    {
        $admin = $this->admin();
        [$document, $quotation] = $this->signedFixtureRecap($admin);

        $response = $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), []);

        $shipment = Shipment::query()->sole();
        $response->assertRedirect(route('admin.deal-execution.show', $shipment));
        $this->assertSame('pending', $shipment->status);
        $this->assertNull($shipment->carrier_id);
        $this->assertNull($shipment->booking_no);
        $this->assertSame((int) $quotation->customer_id, (int) $shipment->customer_id);
        $this->assertSame((int) $quotation->id, (int) $shipment->quotation_id);
        $this->assertSame((int) $document->deal_id, (int) $shipment->deal_id);
        $this->assertSame((int) $document->id, (int) $shipment->deal_document_id);
        $this->assertSame('Steel coils', $shipment->cargo_type);
        $this->assertMatchesRegularExpression('/^HAG-SHP-[0-9A-HJKMNP-TV-Z]{26}$/', $shipment->shipment_no);
        $this->assertSame($document->document_sha256, $shipment->fixture_recap_sha256);
        $this->assertSame('Steel coils', $shipment->fixture_recap_snapshot['content']['commodity']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $shipment->fixture_recap_snapshot_sha256);
        $this->assertDatabaseHas('shipment_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'deal_handoff_completed',
            'source' => 'deal_execution_handoff',
        ]);
        $this->assertDatabaseHas('deal_document_events', [
            'deal_document_id' => $document->id,
            'event' => 'shipment.execution_created',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertRedirect(route('admin.deal-execution.show', $shipment));
        $this->assertSame(1, Shipment::query()->count());
        $this->assertSame(1, $document->events()->where('event', 'shipment.execution_created')->count());
    }

    public function test_carrier_confirmed_booking_is_required_before_shipment_is_marked_booked(): void
    {
        $admin = $this->admin();
        [$document, $quotation] = $this->signedFixtureRecap($admin);
        $carrier = Carrier::create([
            'name' => 'Confirmed HAG Carrier',
            'code' => 'HAGTEST' . Str::upper(Str::random(5)),
            'country' => 'United Arab Emirates',
            'country_code' => 'AE',
            'is_active' => true,
        ]);
        $booking = FreightRateBooking::create([
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'rate_id' => null,
            'source_id' => null,
            'carrier_id' => $carrier->id,
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', 'confirmed booking test'),
            'status' => 'carrier_confirmed',
            'currency' => 'AED',
            'supplier_total' => '9000.00',
            'customer_total' => '10000.00',
            'commission_total' => '1000.00',
            'rate_snapshot' => ['carrier' => $carrier->name],
            'carrier_reference' => 'CARRIER-BOOK-' . Str::upper(Str::random(6)),
            'submitted_at' => now(),
            'confirmed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [
                'freight_rate_booking_id' => $booking->id,
            ])
            ->assertSessionHasNoErrors();

        $shipment = Shipment::query()->sole();
        $this->assertSame('booked', $shipment->status);
        $this->assertSame((int) $carrier->id, (int) $shipment->carrier_id);
        $this->assertSame((int) $booking->id, (int) $shipment->freight_rate_booking_id);
        $this->assertNull($shipment->freight_booking_offer_id);
        $this->assertSame($booking->carrier_reference, $shipment->booking_no);
    }

    public function test_legacy_accepted_and_carrier_confirmed_booking_is_linked_to_execution(): void
    {
        $admin = $this->admin();
        [$document, $quotation] = $this->signedFixtureRecap($admin);
        $carrier = Carrier::create([
            'name' => 'Legacy Confirmed Carrier',
            'code' => 'HAGLEG' . Str::upper(Str::random(5)),
            'country' => 'United Arab Emirates',
            'country_code' => 'AE',
            'is_active' => true,
        ]);
        $offer = QuotationOffer::create([
            'quotation_id' => $quotation->id,
            'carrier_id' => $carrier->id,
            'carrier_amount' => '9000.00',
            'customer_amount' => '10000.00',
            'currency' => 'AED',
            'departure_date' => '2026-12-10',
            'eta' => '2026-12-15',
            'transit_days' => 5,
            'status' => 'approved',
        ]);
        $quotation->forceFill(['approved_offer_id' => $offer->id])->save();

        $legacyBooking = (new FreightBookingOffer)->forceFill([
            'quotation_id' => $quotation->id,
            'revision' => (string) Str::uuid(),
            'amount' => '10000.00',
            'currency' => 'AED',
            'terms' => 'Confirmed freight booking terms',
            'expires_at' => now()->addDay(),
            'accepted_at' => now(),
            'carrier_reference' => 'LEGACY-BOOK-' . Str::upper(Str::random(6)),
            'confirmed_at' => now(),
        ]);
        $legacyBooking->save();

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertSessionHasNoErrors();

        $shipment = Shipment::query()->sole();
        $this->assertSame('booked', $shipment->status);
        $this->assertSame((int) $carrier->id, (int) $shipment->carrier_id);
        $this->assertSame((int) $legacyBooking->id, (int) $shipment->freight_booking_offer_id);
        $this->assertSame($legacyBooking->carrier_reference, $shipment->booking_no);
    }

    public function test_fixture_recap_port_names_must_match_the_linked_quotation_route(): void
    {
        $admin = $this->admin();
        [$document] = $this->signedFixtureRecap($admin, 'Khalifa Port, UAE');

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertSessionHasErrors('execution');

        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_execution_snapshot_is_verified_against_the_signed_source_and_detects_tampering(): void
    {
        $admin = $this->admin();
        [$document] = $this->signedFixtureRecap($admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertSessionHasNoErrors();

        $shipment = Shipment::query()->sole();
        $source = DealDocument::query()->with('parties')->findOrFail($document->id);
        $service = app(DealExecutionHandoffService::class);

        $this->assertTrue($service->verifyShipmentSnapshot($shipment, $source)['valid']);

        $tampered = $shipment->fixture_recap_snapshot;
        $tampered['content']['commodity'] = 'Tampered cargo description';
        DB::table('shipments')->where('id', $shipment->id)->update([
            'fixture_recap_snapshot' => json_encode($tampered, JSON_THROW_ON_ERROR),
        ]);

        $result = $service->verifyShipmentSnapshot($shipment->fresh(), $source);
        $this->assertFalse($result['valid']);
        $this->assertContains('fixture_recap_snapshot_hash_mismatch', $result['issues']);
        $this->assertContains('fixture_recap_snapshot_content_mismatch', $result['issues']);
    }

    public function test_execution_source_links_cannot_be_changed_after_handoff(): void
    {
        $admin = $this->admin();
        [$document] = $this->signedFixtureRecap($admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-execution.store', $document), [])
            ->assertSessionHasNoErrors();

        $shipment = Shipment::query()->sole();
        $originalHash = $shipment->fixture_recap_sha256;

        try {
            $shipment->forceFill(['fixture_recap_sha256' => str_repeat('0', 64)])->save();
            $this->fail('The shipment source hash should be immutable after handoff.');
        } catch (\LogicException $exception) {
            $this->assertSame('Shipment execution source records are immutable.', $exception->getMessage());
        }

        $this->assertSame($originalHash, $shipment->fresh()->fixture_recap_sha256);
    }

    public function test_shipment_schema_contains_source_and_operational_links(): void
    {
        $this->assertTrue(Schema::hasColumns('shipments', [
            'deal_id',
            'deal_document_id',
            'freight_rate_booking_id',
            'freight_booking_offer_id',
            'freight_sailing_id',
            'fixture_recap_sha256',
            'fixture_recap_snapshot',
            'fixture_recap_snapshot_sha256',
        ]));
    }
}
