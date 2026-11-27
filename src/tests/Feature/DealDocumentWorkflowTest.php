<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\DealDocument;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealDocumentWorkflowTest extends TestCase
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
            'company_name' => 'HAG Document Workflow Test',
            'country_code' => 'AE',
        ]);

        return Deal::create([
            'reference' => 'HAG-DOC-TEST-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'collection_model' => 'commission_only',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => null,
            'platform_revenue' => '0.00',
        ]);
    }

    private function ncndaPayload(): array
    {
        return [
            'type' => 'ncnda',
            'title' => 'Mutual NCNDA – Test Deal',
            'language' => 'en',
            'parties' => [
                [
                    'role_key' => 'party_a',
                    'company_name' => 'Party A Trading LLC',
                    'contact_name' => 'Authorized Signatory A',
                    'email' => 'party-a@example.test',
                ],
                [
                    'role_key' => 'party_b',
                    'company_name' => 'Party B Shipping Ltd',
                    'contact_name' => 'Authorized Signatory B',
                    'email' => 'party-b@example.test',
                ],
            ],
            'content' => [
                'protected_contacts' => 'The introduced shipper and nominated carrier contacts.',
                'term_months' => 12,
                'territory' => 'United Arab Emirates and agreed trade lanes',
                'effective_date' => '2026-11-27',
                'legal_reviewed_by' => 'HAG Legal Counsel (Test)',
                'legal_reviewed_at' => now()->toDateString(),
                'agreement_text' => 'This is test wording supplied after legal review. It is not a production legal precedent.',
            ],
        ];
    }

    private function createNcnda(Deal $deal, User $admin): DealDocument
    {
        $this->actingAs($admin)
            ->post(route('admin.deals.documents.store', $deal), $this->ncndaPayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        return DealDocument::query()->sole();
    }

    private function approvedDeal(User $admin): array
    {
        $deal = $this->deal();
        $agreement = $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => 'percent',
            'commission_bps' => 250,
            'fixed_fee' => null,
            'terms' => 'Approved brokerage commission terms for this test transaction.',
        ]);

        app(DealApprovalService::class)->approve($deal, $agreement, $admin->id);

        return [$deal->fresh(), $agreement->fresh(), DealCommission::query()->sole()];
    }

    public function test_admin_can_create_ncnda_draft_with_parties_version_and_audit(): void
    {
        $deal = $this->deal();
        $admin = $this->admin();

        $document = $this->createNcnda($deal, $admin);

        $this->assertSame('ncnda', $document->type);
        $this->assertSame('draft', $document->status);
        $this->assertNull($document->locked_at);
        $this->assertSame(2, $document->parties()->count());
        $this->assertSame(1, $document->versions()->count());
        $this->assertSame('draft.created', $document->events()->latest('id')->value('event'));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $document->versions()->first()->snapshot_sha256);
    }

    public function test_admin_can_create_fixture_recap_with_maritime_terms(): void
    {
        $deal = $this->deal();
        $payload = $this->ncndaPayload();
        $payload['type'] = 'fixture_recap';
        $payload['title'] = 'Fixture Recap – Test Cargo';
        $payload['content'] = [
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
            'additional_terms' => 'Subject to final operational confirmation.',
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.deals.documents.store', $deal), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $document = DealDocument::query()->sole();
        $this->assertSame('fixture_recap', $document->type);
        $this->assertSame('2500', $document->content['quantity']);
        $this->assertSame('Jebel Ali, UAE', $document->content['loading_port']);
        $this->assertSame('per metric ton', $document->content['freight_rate_basis']);
        $this->assertSame('USD', $document->content['freight_currency']);
        $this->assertSame(1, $document->versions()->count());
    }

    public function test_fixture_recap_rejects_invalid_laycan_date_order(): void
    {
        $deal = $this->deal();
        $payload = $this->ncndaPayload();
        $payload['type'] = 'fixture_recap';
        $payload['content'] = [
            'commodity' => 'Steel coils',
            'quantity' => '2500',
            'quantity_unit' => 'MT',
            'loading_port' => 'Jebel Ali, UAE',
            'discharge_port' => 'Mombasa, Kenya',
            'laycan_from' => '2026-12-15',
            'laycan_to' => '2026-12-10',
            'freight_currency' => 'USD',
            'payment_terms' => 'Payment against agreed shipping documents.',
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.deals.documents.store', $deal), $payload)
            ->assertSessionHasErrors('content.laycan_to');

        $this->assertDatabaseCount('deal_documents', 0);
    }

    public function test_admin_can_update_only_draft_content_and_each_change_creates_a_version(): void
    {
        $deal = $this->deal();
        $admin = $this->admin();
        $document = $this->createNcnda($deal, $admin);
        $payload = $this->ncndaPayload();
        $payload['content']['term_months'] = 18;
        $payload['parties'][1]['company_name'] = 'Updated Counterparty Ltd';

        $this->actingAs($admin)
            ->put(route('admin.deal-documents.update', $document), $payload)
            ->assertRedirect(route('admin.deal-documents.show', $document))
            ->assertSessionHasNoErrors();

        $document->refresh();
        $this->assertSame(18, $document->content['term_months']);
        $this->assertSame('Updated Counterparty Ltd', $document->parties()->where('role_key', 'party_b')->value('company_name'));
        $this->assertSame(2, $document->versions()->count());
        $this->assertSame('draft.updated', $document->events()->latest('id')->value('event'));
    }

    public function test_preparing_document_locks_content_and_party_identity(): void
    {
        $deal = $this->deal();
        $admin = $this->admin();
        $document = $this->createNcnda($deal, $admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-documents.prepare', $document))
            ->assertRedirect(route('admin.deal-documents.show', $document))
            ->assertSessionHasNoErrors();

        $document->refresh();
        $this->assertSame('ready_for_signature', $document->status);
        $this->assertNotNull($document->locked_at);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $document->document_sha256);

        $payload = $this->ncndaPayload();
        $payload['content']['term_months'] = 24;
        $this->actingAs($admin)
            ->put(route('admin.deal-documents.update', $document), $payload)
            ->assertForbidden();

        $this->expectException(\LogicException::class);
        $document->parties()->firstOrFail()->forceFill([
            'company_name' => 'Tampered after lock',
        ])->save();
    }

    public function test_prepared_document_integrity_verifies_and_detects_hash_tampering(): void
    {
        $deal = $this->deal();
        $admin = $this->admin();
        $document = $this->createNcnda($deal, $admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-documents.prepare', $document))
            ->assertSessionHasNoErrors();

        $document->refresh();
        $verifier = app(\App\Services\Revenue\DealDocumentIntegrityVerifier::class);
        $this->assertTrue($verifier->verify($document)['valid']);

        // JSON object-key ordering may differ between SQLite and MySQL.
        // Reordering persisted keys must not invalidate canonical SHA-256 hashes.
        $version = $document->versions()->firstOrFail();
        $reorderedSnapshot = array_reverse($version->snapshot, true);
        \Illuminate\Support\Facades\DB::table('deal_document_versions')
            ->where('id', $version->id)
            ->update(['snapshot' => json_encode($reorderedSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        $createdEvent = $document->events()->where('event', 'draft.created')->firstOrFail();
        $reorderedEventPayload = array_reverse($createdEvent->payload, true);
        \Illuminate\Support\Facades\DB::table('deal_document_events')
            ->where('id', $createdEvent->id)
            ->update(['payload' => json_encode($reorderedEventPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        $this->assertTrue($verifier->verify($document->fresh())['valid']);

        // Hashes also bind version/event ownership and attribution metadata.
        \Illuminate\Support\Facades\DB::table('deal_document_versions')
            ->where('id', $version->id)
            ->update(['actor_id' => null]);
        $this->assertContains(
            'version_hash_mismatch',
            $verifier->verify($document->fresh())['issues']
        );
        \Illuminate\Support\Facades\DB::table('deal_document_versions')
            ->where('id', $version->id)
            ->update(['actor_id' => $admin->id]);

        \Illuminate\Support\Facades\DB::table('deal_document_events')
            ->where('id', $createdEvent->id)
            ->update(['actor_id' => null]);
        $this->assertContains(
            'audit_hash_mismatch',
            $verifier->verify($document->fresh())['issues']
        );
        \Illuminate\Support\Facades\DB::table('deal_document_events')
            ->where('id', $createdEvent->id)
            ->update(['actor_id' => $admin->id]);

        $this->assertTrue($verifier->verify($document->fresh())['valid']);

        \Illuminate\Support\Facades\DB::table('deal_documents')
            ->where('id', $document->id)
            ->update(['document_sha256' => str_repeat('0', 64)]);

        $this->assertContains(
            'document_hash_mismatch',
            $verifier->verify($document->fresh())['issues']
        );

        \Illuminate\Support\Facades\DB::table('deal_document_events')
            ->where('deal_document_id', $document->id)
            ->where('event', 'document.prepared')
            ->update(['payload_sha256' => str_repeat('f', 64)]);

        $this->assertContains(
            'audit_hash_mismatch',
            $verifier->verify($document->fresh())['issues']
        );
    }

    public function test_signed_copies_are_recorded_and_document_completes_after_all_parties_sign(): void
    {
        Storage::fake('local');
        $deal = $this->deal();
        $admin = $this->admin();
        $document = $this->createNcnda($deal, $admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-documents.prepare', $document))
            ->assertSessionHasNoErrors();

        $parties = $document->parties()->orderBy('signing_order')->get();
        $this->actingAs($admin)
            ->post(route('admin.deal-documents.parties.signature', [$document, $parties[1]]), [
                'signed_by_name' => 'Authorized Signatory 2',
                'signed_by_title' => 'Director',
                'signed_at' => now()->toDateString(),
                'evidence' => UploadedFile::fake()->image('out-of-order.png'),
            ])
            ->assertSessionHasErrors('party');
        $this->assertSame(0, $document->parties()->where('status', 'signed')->count());
        $this->assertSame(0, count(Storage::disk('local')->allFiles('deal-documents/' . $document->id . '/signatures')));

        foreach ($parties as $index => $party) {
            $this->actingAs($admin)
                ->post(route('admin.deal-documents.parties.signature', [$document, $party]), [
                    'signed_by_name' => 'Authorized Signatory ' . ($index + 1),
                    'signed_by_title' => 'Director',
                    'signed_at' => now()->toDateString(),
                    'evidence' => UploadedFile::fake()->image('signed-copy-' . $index . '.png'),
                ])
                ->assertRedirect(route('admin.deal-documents.show', $document))
                ->assertSessionHasNoErrors();

            $document->refresh();
            $this->assertSame($index === 0 ? 'partially_signed' : 'signed', $document->status);
        }

        $document->refresh();
        $this->assertNotNull($document->signed_at);
        $this->assertSame(2, $document->parties()->where('status', 'signed')->count());
        $this->assertSame(2, $document->events()->where('event', 'party.signature_received')->count());
        $this->assertSame(1, $document->events()->where('event', 'document.fully_signed')->count());

        $signedParty = $document->parties()->firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signedParty->signature_evidence_sha256);
        $this->assertTrue(Storage::disk('local')->exists($signedParty->signature_evidence_path));
        $verifier = app(\App\Services\Revenue\DealDocumentIntegrityVerifier::class);
        $this->assertTrue($verifier->verify($document)['valid']);

        \Illuminate\Support\Facades\DB::table('deal_document_parties')
            ->where('id', $signedParty->id)
            ->update(['signed_by_title' => 'Tampered Title']);
        $this->assertContains(
            'signature_audit_party_mismatch',
            $verifier->verify($document->fresh())['issues']
        );
        \Illuminate\Support\Facades\DB::table('deal_document_parties')
            ->where('id', $signedParty->id)
            ->update(['signed_by_title' => 'Director']);

        Storage::disk('local')->put($signedParty->signature_evidence_path, 'tampered evidence');
        $this->assertContains(
            'signature_evidence_hash_mismatch',
            $verifier->verify($document->fresh())['issues']
        );
    }

    public function test_signature_upload_is_blocked_when_prepared_document_integrity_fails(): void
    {
        Storage::fake('local');
        $deal = $this->deal();
        $admin = $this->admin();
        $document = $this->createNcnda($deal, $admin);

        $this->actingAs($admin)
            ->post(route('admin.deal-documents.prepare', $document))
            ->assertSessionHasNoErrors();

        \Illuminate\Support\Facades\DB::table('deal_documents')
            ->where('id', $document->id)
            ->update(['document_sha256' => str_repeat('a', 64)]);

        $party = $document->parties()->orderBy('signing_order')->firstOrFail();
        $response = $this->actingAs($admin)
            ->post(route('admin.deal-documents.parties.signature', [$document, $party]), [
                'signed_by_name' => 'Authorized Signatory',
                'signed_by_title' => 'Director',
                'signed_at' => now()->toDateString(),
                'evidence' => UploadedFile::fake()->image('signed-copy.png'),
            ]);

        $response->assertSessionHasErrors('document');
        $this->assertSame('ready_for_signature', $document->fresh()->status);
        $this->assertSame(0, $document->parties()->where('status', 'signed')->count());
        $this->assertSame(0, $document->events()->where('event', 'party.signature_received')->count());
        $this->assertSame(0, count(Storage::disk('local')->allFiles('deal-documents/' . $document->id . '/signatures')));
    }

    public function test_commission_addendum_uses_only_approved_immutable_commission_snapshot(): void
    {
        $admin = $this->admin();
        [$deal, $agreement, $commission] = $this->approvedDeal($admin);
        $payload = $this->ncndaPayload();
        $payload['type'] = 'commission_addendum';
        $payload['title'] = 'Commission Addendum';
        $payload['content'] = [
            'commission_amount' => '0.01',
            'currency' => 'ZZZ',
        ];

        $this->actingAs($admin)
            ->post(route('admin.deals.documents.store', $deal), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $document = DealDocument::query()->sole();
        $snapshot = $document->financial_snapshot;

        $this->assertSame((int) $agreement->id, (int) $snapshot['agreement']['id']);
        $this->assertSame((int) $commission->id, (int) $snapshot['commission']['id']);
        $this->assertSame('125.00', $snapshot['commission']['amount']);
        $this->assertSame('AED', $snapshot['commission']['currency']);
        $this->assertArrayNotHasKey('commission_amount', $document->content);
        $this->assertSame('approved_deal_agreement_and_immutable_commission', $snapshot['source']);

        $this->actingAs($admin)
            ->post(route('admin.deal-documents.prepare', $document))
            ->assertSessionHasNoErrors();

        $this->assertSame('ready_for_signature', $document->fresh()->status);
    }

    public function test_guests_and_customers_cannot_access_deal_documents(): void
    {
        $deal = $this->deal();
        $this->get(route('admin.deal-documents.index'))
            ->assertRedirect(route('login'));

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('admin.deal-documents.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('admin.deals.documents.store', $deal), $this->ncndaPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('deal_documents', 0);
    }
}
