<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealCommission;
use App\Models\InvoiceDraft;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealApprovalService;
use App\Services\Revenue\InvoiceDraftService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceDraftsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $roleName = 'admin'): User
    {
        $user = User::factory()->create();
        $user->addRole(Role::firstOrCreate(['name' => $roleName], ['display_name' => $roleName]));
        return $user;
    }

    private function commission(User $actor, string $currency = 'AED'): DealCommission
    {
        $customer = Customer::create(['company_name' => 'Original customer', 'email' => 'billing@example.test']);
        $deal = Deal::create([
            'reference' => 'INV-DEAL-'.Str::upper(Str::random(12)), 'customer_id' => $customer->id,
            'collection_model' => 'full_service', 'status' => 'draft', 'currency' => $currency,
            'deal_amount' => '1000.00', 'supplier_cost' => '800.00', 'platform_revenue' => '0.00',
        ]);
        $agreement = $deal->agreements()->create([
            'version' => 1, 'status' => 'draft', 'fee_kind' => 'fixed',
            'fixed_fee' => '125.50', 'terms' => 'Approved brokerage terms',
        ]);
        return (new DealApprovalService())->approve($deal, $agreement, $actor->id);
    }

    private function draft(User $admin): InvoiceDraft
    {
        return (new InvoiceDraftService())->create($this->commission($admin), $admin->id);
    }

    private function financialSnapshot(): array
    {
        return collect(['deals', 'deal_agreements', 'deal_commissions', 'deal_audits'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()])->all();
    }

    public function test_preparation_uses_approved_commission_and_does_not_change_source_records(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        $before = $this->financialSnapshot();
        $response = $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission));
        $draft = InvoiceDraft::sole();
        $response->assertRedirect(route('admin.invoice-drafts.show', $draft))->assertSessionHasNoErrors();
        $this->assertSame('125.50', $draft->amount);
        $this->assertSame('AED', $draft->currency);
        $this->assertSame('draft', $draft->status);
        $this->assertEquals($commission->id, $draft->deal_commission_id);
        $this->assertEquals($commission->deal->customer_id, $draft->customer_id);
        $this->assertTrue((new InvoiceDraftService())->hasValidSnapshot($draft));
        $this->assertSame($before, $this->financialSnapshot());
    }

    public function test_repeated_preparation_returns_same_draft_and_keeps_saved_notes(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission))->assertRedirect();
        $draft = InvoiceDraft::sole();
        $this->patch(route('admin.invoice-drafts.update', $draft), ['notes' => 'Keep these notes', 'due_date' => '2026-12-15'])->assertSessionHasNoErrors();
        $this->post(route('admin.invoice-drafts.store', $commission))->assertRedirect(route('admin.invoice-drafts.show', $draft));
        $this->assertDatabaseCount('invoice_drafts', 1);
        $this->assertSame('Keep these notes', $draft->fresh()->notes);
        $this->assertSame('2026-12-15', $draft->fresh()->due_date->format('Y-m-d'));
    }

    public function test_request_cannot_override_amount_currency_or_customer(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission), [
            'amount' => '0.01', 'currency' => 'USD', 'customer_id' => 999999,
        ])->assertSessionHasErrors(['amount', 'currency', 'customer_id']);
        $this->assertDatabaseCount('invoice_drafts', 0);
    }

    public function test_unapproved_source_cannot_prepare_a_draft(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        DB::table('deals')->where('id', $commission->deal_id)->update(['status' => 'draft', 'approved_at' => null]);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission))->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('invoice_drafts', 0);
    }

    public function test_tampered_commission_amount_is_rejected(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        DB::table('deal_commissions')->where('id', $commission->id)->update(['amount' => '1.00']);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission))->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('invoice_drafts', 0);
    }

    public function test_missing_approval_audit_is_rejected(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        DB::table('deal_audits')->where('deal_id', $commission->deal_id)->delete();
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission))->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('invoice_drafts', 0);
    }

    public function test_reversed_commission_is_not_eligible(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        DB::table('deal_commissions')->where('id', $commission->id)->update(['status' => 'reversed']);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $commission))->assertSessionHasErrors('invoice');
        $this->assertDatabaseCount('invoice_drafts', 0);
    }

    public function test_only_metadata_is_editable_without_changing_financial_snapshots(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $before = $this->financialSnapshot();
        $snapshot = $draft->snapshot;
        $hash = $draft->snapshot_sha256;
        $this->actingAs($admin)->patch(route('admin.invoice-drafts.update', $draft), [
            'due_date' => '2026-12-01', 'notes' => 'Review before issue',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $updated = $draft->fresh();
        $this->assertSame('Review before issue', $updated->notes);
        $this->assertSame($snapshot, $updated->snapshot);
        $this->assertSame($hash, $updated->snapshot_sha256);
        $this->assertSame($before, $this->financialSnapshot());
    }

    public function test_metadata_update_cannot_change_financial_ownership_or_status(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $this->actingAs($admin)->patch(route('admin.invoice-drafts.update', $draft), [
            'amount' => '5.00', 'deal_id' => 99999, 'status' => 'paid',
        ])->assertSessionHasErrors(['amount', 'deal_id', 'status']);
        $this->assertSame('125.50', $draft->fresh()->amount);
        $this->assertSame('draft', $draft->fresh()->status);
    }

    public function test_invalid_metadata_is_rejected(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $this->actingAs($admin)->patch(route('admin.invoice-drafts.update', $draft), [
            'due_date' => 'not-a-date', 'notes' => str_repeat('x', 4001),
        ])->assertSessionHasErrors(['due_date', 'notes']);
        $this->assertNull($draft->fresh()->notes);
    }

    public function test_tampered_draft_is_detected_and_cannot_be_edited(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        DB::table('invoice_drafts')->where('id', $draft->id)->update(['amount' => '1.00']);
        $this->actingAs($admin)->get(route('admin.invoice-drafts.show', $draft))
            ->assertOk()->assertViewHas('snapshotValid', false);
        $this->patch(route('admin.invoice-drafts.update', $draft), ['notes' => 'Blocked'])
            ->assertSessionHasErrors('invoice');
        $this->assertNull($draft->fresh()->notes);
    }

    public function test_draft_model_blocks_financial_changes(): void
    {
        $draft = $this->draft($this->admin());
        $this->expectException(\LogicException::class);
        $draft->update(['amount' => '0.01']);
    }

    public function test_draft_model_blocks_deletion(): void
    {
        $draft = $this->draft($this->admin());
        $this->expectException(\LogicException::class);
        $draft->delete();
    }

    public function test_database_unique_constraint_prevents_second_draft_for_same_commission(): void
    {
        $draft = $this->draft($this->admin());
        $attributes = $draft->getAttributes();
        unset($attributes['id']);
        $attributes['reference'] = 'ANOTHER-DRAFT-REFERENCE';
        $this->expectException(QueryException::class);
        DB::table('invoice_drafts')->insert($attributes);
    }

    public function test_customer_profile_edits_do_not_change_existing_draft_snapshot(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $draft->customer->update(['company_name' => 'Changed customer']);
        $this->actingAs($admin)->post(route('admin.invoice-drafts.store', $draft->commission))
            ->assertRedirect(route('admin.invoice-drafts.show', $draft));
        $this->assertSame('Original customer', $draft->fresh()->snapshot['customer']['company_name']);
    }

    public function test_customer_snapshot_contains_no_supplier_cost_or_platform_revenue(): void
    {
        $draft = $this->draft($this->admin());
        $this->assertArrayNotHasKey('supplier_cost', $draft->snapshot);
        $this->assertArrayNotHasKey('platform_revenue', $draft->snapshot);
        $this->assertSame('Original customer', $draft->snapshot['customer']['company_name']);
    }

    public function test_json_key_reordering_does_not_break_snapshot_verification(): void
    {
        $draft = $this->draft($this->admin());
        $snapshot = array_reverse($draft->snapshot, true);
        $snapshot['customer'] = array_reverse($snapshot['customer'], true);
        DB::table('invoice_drafts')->where('id', $draft->id)->update(['snapshot' => json_encode($snapshot)]);
        $this->assertTrue((new InvoiceDraftService())->hasValidSnapshot($draft->fresh()));
    }

    public function test_both_languages_and_admin_roles_can_open_draft_pages(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        foreach ([$admin, $this->admin('super-admin')] as $actor) {
            $this->actingAs($actor);
            foreach (['ar', 'en'] as $locale) {
                $this->withSession(['locale' => $locale])->get(route('admin.invoice-drafts.index'))->assertOk()->assertSee($draft->reference);
                $this->get(route('admin.invoice-drafts.show', $draft))->assertOk()->assertSee($draft->reference)->assertDontSee('invoice_drafts.title');
            }
        }
    }

    public function test_guests_and_customers_cannot_access_internal_drafts_even_their_own(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $this->get(route('admin.invoice-drafts.index'))->assertRedirect(route('login'));
        $customer = User::factory()->create(['customer_id' => $draft->customer_id]);
        $this->actingAs($customer)->get(route('admin.invoice-drafts.index'))->assertForbidden();
        $this->get(route('admin.invoice-drafts.show', $draft))->assertForbidden();
        $this->post(route('admin.invoice-drafts.store', $draft->commission))->assertForbidden();
        $this->patch(route('admin.invoice-drafts.update', $draft), ['notes' => 'No access'])->assertForbidden();
        $this->assertNull($draft->fresh()->notes);
    }

    public function test_financial_report_links_to_preparation_then_existing_draft(): void
    {
        $admin = $this->admin();
        $commission = $this->commission($admin);
        $this->actingAs($admin)->get(route('admin.finance.show', $commission->deal))
            ->assertOk()->assertSee(route('admin.invoice-drafts.store', $commission), false);
        $draft = (new InvoiceDraftService())->create($commission, $admin->id);
        $this->get(route('admin.finance.show', $commission->deal))
            ->assertOk()->assertSee(route('admin.invoice-drafts.show', $draft), false);
    }

    public function test_drafts_have_no_delete_or_issue_endpoints(): void
    {
        $admin = $this->admin();
        $draft = $this->draft($admin);
        $this->actingAs($admin)->delete(route('admin.invoice-drafts.show', $draft))->assertStatus(405);
        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertDatabaseCount('invoice_drafts', 1);
    }
}
