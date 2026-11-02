<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\InvoiceDraft;
use App\Models\IssuedInvoice;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealApprovalService;
use App\Services\Revenue\InvoiceDraftService;
use App\Services\Revenue\InvoiceIssuanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceIssuanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->addRole(Role::firstOrCreate(['name' => $role], ['display_name' => $role]));
        return $user;
    }

    private function company(): CompanySetting
    {
        return CompanySetting::updateOrCreate([], [
            'company_name' => 'HAG Test Supplier', 'company_name_ar' => 'المورد التجريبي',
            'address' => 'Supplier Test Address', 'city' => 'Dubai', 'country' => 'UAE',
            'tax_number' => '100000000000001',
        ]);
    }

    private function draft(User $actor, string $currency = 'AED'): InvoiceDraft
    {
        $customer = Customer::create(['company_name' => 'Invoice Test Customer']);
        $deal = Deal::create([
            'reference' => 'ISSUE-'.Str::upper(Str::random(12)), 'customer_id' => $customer->id,
            'collection_model' => 'full_service', 'currency' => $currency,
            'deal_amount' => '1000.00', 'supplier_cost' => '800.00', 'platform_revenue' => '0.00',
        ]);
        $agreement = $deal->agreements()->create([
            'version' => 1, 'status' => 'draft', 'fee_kind' => 'fixed',
            'fixed_fee' => '125.50', 'terms' => 'Approved invoice fixture terms',
        ]);
        $commission = (new DealApprovalService())->approve($deal, $agreement, $actor->id);
        return (new InvoiceDraftService())->create($commission, $actor->id);
    }

    private function input(): array
    {
        return [
            'supply_date' => now('Asia/Dubai')->toDateString(),
            'due_date' => now('Asia/Dubai')->addDays(30)->toDateString(),
            'tax_treatment' => 'standard', 'amount_basis' => 'exclusive',
            'recipient_registered' => 'yes', 'recipient_trn' => '100000000000002',
            'recipient_address' => 'Customer Test Address',
            'description_ar' => 'خدمة وساطة تجريبية', 'description_en' => 'Test brokerage service',
            'public_notes' => 'Public invoice note',
        ];
    }

    private function review(User $actor, InvoiceDraft $draft, array $changes = []): array
    {
        return (new InvoiceIssuanceService())->preview($draft, array_replace($this->input(), $changes), $actor->id);
    }

    private function issue(User $actor, InvoiceDraft $draft, array $changes = []): IssuedInvoice
    {
        $review = $this->review($actor, $draft, $changes);
        return (new InvoiceIssuanceService())->issue($draft, $review['token'], $actor->id);
    }

    public function test_preview_does_not_issue_or_reserve_a_number(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $this->actingAs($admin)->withSession(['locale' => 'en'])->post(route('admin.invoices.preview', $draft), $this->input())
            ->assertOk()->assertSee('Test brokerage service')->assertViewHas('token');
        $this->assertDatabaseCount('issued_invoices', 0);
        $this->assertDatabaseCount('invoice_sequences', 0);
    }

    public function test_explicit_confirmation_issues_once_without_changing_financial_source(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $snapshot = fn () => collect(['deals','deal_agreements','deal_commissions','deal_audits','invoice_drafts'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()])->all();
        $before = $snapshot(); $review = $this->review($admin, $draft);
        $response = $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1]);
        $invoice = IssuedInvoice::sole();
        $response->assertRedirect(route('admin.invoices.show', $invoice));
        $this->assertStringStartsWith('HAG-INV-'.now('Asia/Dubai')->format('Y').'-', $invoice->reference);
        $this->assertSame('131.78', $invoice->total);
        $this->assertSame('6.28', $invoice->tax_amount);
        $this->assertTrue((new InvoiceIssuanceService())->hasValidSnapshot($invoice));
        $this->assertSame($before, $snapshot());
        $this->assertSame('pending', $draft->commission->fresh()->status);
    }

    public function test_duplicate_submission_reuses_invoice_without_consuming_second_number(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $review = $this->review($admin, $draft); $service = new InvoiceIssuanceService();
        $first = $service->issue($draft, $review['token'], $admin->id);
        $second = $service->issue($draft, $review['token'], $admin->id);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('issued_invoices', 1);
        $this->assertEquals(1, DB::table('invoice_sequences')->value('last_number'));
    }

    public function test_different_drafts_get_distinct_ordered_numbers(): void
    {
        $this->company(); $admin = $this->admin();
        $first = $this->issue($admin, $this->draft($admin));
        $second = $this->issue($admin, $this->draft($admin));
        $this->assertEquals(1, $first->sequence_number);
        $this->assertEquals(2, $second->sequence_number);
        $this->assertNotSame($first->reference, $second->reference);
    }

    public function test_missing_supplier_trn_blocks_preview(): void
    {
        $this->company()->update(['tax_number' => null]); $admin = $this->admin(); $draft = $this->draft($admin);
        $this->actingAs($admin)->get(route('admin.invoices.review', $draft))->assertOk()->assertViewHas('issuerReady', false);
        $this->post(route('admin.invoices.preview', $draft), $this->input())->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_tax_treatment_and_inclusive_basis_must_be_selected(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $data = $this->input(); unset($data['tax_treatment'], $data['amount_basis']);
        $this->actingAs($admin)->post(route('admin.invoices.preview', $draft), $data)
            ->assertSessionHasErrors(['tax_treatment', 'amount_basis']);
    }

    public function test_zero_rate_requires_recorded_basis(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $this->actingAs($admin)->post(route('admin.invoices.preview', $draft), array_replace($this->input(), ['tax_treatment' => 'zero']))
            ->assertSessionHasErrors('tax_reason');
    }

    public function test_registered_recipient_requires_trn_and_address(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $data = $this->input(); unset($data['recipient_trn'], $data['recipient_address']);
        $this->actingAs($admin)->post(route('admin.invoices.preview', $draft), $data)
            ->assertSessionHasErrors(['recipient_trn', 'recipient_address']);
    }

    public function test_foreign_currency_requires_explicit_rate_and_reference(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin, 'USD');
        $this->actingAs($admin)->post(route('admin.invoices.preview', $draft), $this->input())
            ->assertSessionHasErrors(['exchange_rate', 'exchange_rate_date', 'exchange_rate_reference']);
        $invoice = $this->issue($admin, $draft, ['exchange_rate' => '3.6725', 'exchange_rate_date' => $this->input()['supply_date'], 'exchange_rate_reference' => 'Test fixture rate']);
        $this->assertSame('USD', $invoice->currency);
        $this->assertSame('3.67250000', $invoice->exchange_rate);
        $this->assertSame('23.06', $invoice->tax_aed);
        $this->assertSame('483.96', $invoice->total_aed);
    }

    public function test_tampered_token_cannot_issue(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => 'tampered', 'confirm' => 1])->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
        $this->assertDatabaseCount('invoice_sequences', 0);
    }

    public function test_token_cannot_be_used_for_another_draft_or_actor(): void
    {
        $this->company(); $admin = $this->admin(); $other = $this->admin();
        $draft = $this->draft($admin); $otherDraft = $this->draft($admin); $review = $this->review($admin, $draft);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $otherDraft), ['review_token' => $review['token'], 'confirm' => 1])->assertSessionHasErrors('invoice');
        $this->actingAs($other)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1])->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_expired_review_is_rejected_before_number_allocation(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        $this->travel(16)->minutes();
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1])->assertSessionHasErrors('invoice')->assertRedirect(route('admin.invoices.review', $draft));
        $this->travelBack();
        $this->assertDatabaseCount('issued_invoices', 0);
        $this->assertDatabaseCount('invoice_sequences', 0);
    }

    public function test_supplier_change_requires_new_review(): void
    {
        $company = $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        $company->update(['address' => 'Changed after preview']);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1])->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_tampered_source_after_preview_blocks_issuance(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        DB::table('deal_commissions')->where('id', $draft->deal_commission_id)->update(['amount' => '0.01']);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1])->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_confirmation_is_required(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token']])->assertSessionHasErrors('confirm');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_issued_snapshot_keeps_supplier_and_customer_data_and_excludes_internal_notes(): void
    {
        $company = $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $draft->update(['notes' => 'SECRET-INTERNAL-NOTE']); $invoice = $this->issue($admin, $draft);
        $company->update(['company_name' => 'Changed supplier']); $draft->customer->update(['company_name' => 'Changed customer']);
        $this->assertSame('HAG Test Supplier', $invoice->fresh()->snapshot['issuer']['name']);
        $this->assertSame('Invoice Test Customer', $invoice->fresh()->snapshot['customer_name']);
        $this->assertStringNotContainsString('SECRET-INTERNAL-NOTE', json_encode($invoice->snapshot));
        $this->assertStringNotContainsString('supplier_cost', json_encode($invoice->snapshot));
    }

    public function test_issued_invoice_and_source_draft_cannot_be_edited_or_deleted(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $invoice = $this->issue($admin, $draft);
        $this->actingAs($admin)->patch(route('admin.invoice-drafts.update', $draft), ['notes' => 'Blocked'])->assertSessionHasErrors('invoice');
        foreach ([fn () => $invoice->update(['total' => '1.00']), fn () => $invoice->delete(), fn () => $draft->fresh()->update(['notes' => 'Blocked'])] as $operation) {
            try { $operation(); $this->fail('Expected immutable record.'); } catch (\LogicException $exception) { $this->assertNotEmpty($exception->getMessage()); }
        }
        $this->assertDatabaseHas('issued_invoices', ['id' => $invoice->id]);
    }

    public function test_admin_roles_can_view_both_languages_and_customers_cannot_access(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $invoice = $this->issue($admin, $draft);
        foreach ([$admin, $this->admin('super-admin')] as $actor) {
            $this->actingAs($actor);
            foreach (['ar','en'] as $locale) {
                $this->withSession(['locale' => $locale])->get(route('admin.invoices.index'))->assertOk()->assertSee($invoice->reference);
                $this->get(route('admin.invoices.show', $invoice))->assertOk()->assertViewHas('snapshotValid', true);
            }
        }
        $this->actingAs(User::factory()->create(['customer_id' => $draft->customer_id]));
        $this->get(route('admin.invoices.show', $invoice))->assertForbidden();
        $this->get(route('admin.invoices.review', $draft))->assertForbidden();
        $this->post(route('admin.invoices.preview', $draft), $this->input())->assertForbidden();
        $this->post(route('admin.invoices.issue', $draft), ['review_token' => 'no', 'confirm' => 1])->assertForbidden();
    }

    public function test_invoice_tampering_is_flagged(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $invoice = $this->issue($admin, $draft);
        DB::table('issued_invoices')->where('id', $invoice->id)->update(['total' => '0.01']);
        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertOk()->assertViewHas('snapshotValid', false);
    }

    public function test_client_cannot_override_reviewed_amounts_on_issue(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        $this->actingAs($admin)->post(route('admin.invoices.issue', $draft), [
            'review_token' => $review['token'], 'confirm' => 1, 'total' => '0.01', 'currency' => 'USD',
        ])->assertRedirect();
        $this->assertSame('131.78', IssuedInvoice::sole()->total);
        $this->assertSame('AED', IssuedInvoice::sole()->currency);
    }

    public function test_review_redirects_to_existing_invoice_after_issue(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $invoice = $this->issue($admin, $draft);
        $this->actingAs($admin)->get(route('admin.invoices.review', $draft))->assertRedirect(route('admin.invoices.show', $invoice));
        $this->get(route('admin.invoice-drafts.show', $draft))->assertOk()->assertSee($invoice->reference);
    }

    public function test_snapshot_verification_does_not_depend_on_default_php_timezone(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $timezone = date_default_timezone_get();
        try { date_default_timezone_set('Asia/Dubai'); $this->assertTrue((new InvoiceIssuanceService())->hasValidSnapshot($invoice->fresh())); }
        finally { date_default_timezone_set($timezone); }
    }

    public function test_guests_cannot_review_or_issue(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        $this->get(route('admin.invoices.index'))->assertRedirect(route('login'));
        $this->get(route('admin.invoices.review', $draft))->assertRedirect(route('login'));
        $this->post(route('admin.invoices.issue', $draft), ['review_token' => $review['token'], 'confirm' => 1])->assertRedirect(route('login'));
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_rehashed_draft_amount_must_still_match_the_original_commission(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin);
        $snapshot = $draft->snapshot; $snapshot['amount'] = '0.01';
        $sort = function (array $data) use (&$sort): array {
            foreach ($data as $key => $item) if (is_array($item)) $data[$key] = $sort($item);
            ksort($data); return $data;
        };
        $hash = hash('sha256', json_encode($sort($snapshot), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        DB::table('invoice_drafts')->where('id', $draft->id)->update(['amount' => '0.01', 'snapshot' => json_encode($snapshot), 'snapshot_sha256' => $hash]);
        $this->assertTrue((new InvoiceDraftService())->hasValidSnapshot($draft->fresh()));
        $this->actingAs($admin)->post(route('admin.invoices.preview', $draft), $this->input())->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('issued_invoices', 0);
    }

    public function test_invoice_number_uses_dubai_calendar_year(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-12-31 22:30:00', 'UTC'));
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $this->assertStringStartsWith('HAG-INV-2027-', $invoice->reference);
        $this->assertSame('2027-01-01', $invoice->snapshot['issue_date']);
        $this->travelBack();
    }

    public function test_failed_invoice_insert_rolls_back_number_allocation(): void
    {
        $this->company(); $admin = $this->admin(); $draft = $this->draft($admin); $review = $this->review($admin, $draft);
        DB::listen(function ($query): void {
            if (str_contains(strtolower($query->sql), 'insert into') && str_contains($query->sql, 'issued_invoices')) {
                throw new \RuntimeException('Simulated issue failure');
            }
        });
        try {
            (new InvoiceIssuanceService())->issue($draft, $review['token'], $admin->id);
            $this->fail('Expected simulated failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated issue failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('issued_invoices', 0);
        $this->assertDatabaseCount('invoice_sequences', 0);
    }
}
