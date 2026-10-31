<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function approver(): User
    {
        $user = User::factory()->create([
            'role' => 'super-admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        return $user;
    }

    private function deal(
        string $model = 'full_service',
        string $status = 'draft'
    ): Deal {
        $customer = Customer::create([
            'company_name' => 'HAG Approval Test',
            'country_code' => 'AE',
        ]);

        return Deal::create([
            'reference' => 'HAG-APP-' . Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => $model,
            'status' => $status,
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => $model === 'full_service'
                ? '4000.00' : null,
            'platform_revenue' => '0.00',
        ]);
    }

    private function agreement(
        Deal $deal,
        string $kind = 'percent'
    ): DealAgreement {
        return $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => $kind,
            'commission_bps' => $kind === 'percent' ? 250 : null,
            'fixed_fee' => $kind === 'fixed' ? '175.00' : null,
            'terms' => 'The parties agree to the brokerage conditions.',
        ]);
    }

    private function approve(
        Deal $deal,
        DealAgreement $agreement
    ) {
        return $this->patch(
            route('admin.deals.agreements.approve', [
                $deal,
                $agreement,
            ])
        );
    }

    public function test_percentage_approval_creates_pending_commission(): void
    {
        $deal = $this->deal('commission_only');
        $agreement = $this->agreement($deal);

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $commission = DealCommission::query()->sole();

        $this->assertEquals('125.00', $commission->amount);
        $this->assertEquals('5000.00', $commission->basis_amount);
        $this->assertSame(250, $commission->rate_bps);
        $this->assertSame('AED', $commission->currency);
        $this->assertSame('pending', $commission->status);
        $this->assertNull($commission->earned_at);

        $this->assertSame('approved', $deal->fresh()->status);
        $this->assertNotNull($deal->fresh()->approved_at);
    }

    public function test_fixed_fee_approval_creates_correct_amount(): void
    {
        $deal = $this->deal('commission_only');
        $agreement = $this->agreement($deal, 'fixed');

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $commission = DealCommission::query()->sole();

        $this->assertEquals('175.00', $commission->amount);
        $this->assertNull($commission->rate_bps);
    }

    public function test_spread_approval_uses_supplier_cost(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal, 'spread');

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $this->assertEquals(
            '1000.00',
            DealCommission::query()->sole()->amount
        );
    }

    public function test_approved_agreement_has_integrity_hash_and_actor(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);
        $admin = $this->approver();

        $this->actingAs($admin);

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $approved = $agreement->fresh();

        $this->assertSame('approved', $approved->status);
        $this->assertEquals($admin->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{64}$/',
            $approved->content_sha256
        );
    }

    public function test_second_approval_is_rejected(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $this->approve($deal, $agreement)
            ->assertSessionHasErrors('agreement');

        $this->assertDatabaseCount('deal_commissions', 1);
    }

    public function test_agreement_from_another_deal_is_rejected(): void
    {
        $deal = $this->deal();
        $otherDeal = $this->deal();
        $agreement = $this->agreement($otherDeal);

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertNotFound();

        $this->assertDatabaseCount('deal_commissions', 0);
        $this->assertSame('draft', $deal->fresh()->status);
    }

    public function test_non_draft_deal_cannot_be_approved(): void
    {
        $deal = $this->deal('full_service', 'approved');
        $agreement = $this->agreement($deal);

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertSessionHasErrors('agreement');

        $this->assertDatabaseCount('deal_commissions', 0);
    }

    public function test_customer_and_guest_cannot_approve(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);

        $this->approve($deal, $agreement)
            ->assertRedirect(route('login'));

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer);

        $this->approve($deal, $agreement)
            ->assertForbidden();

        $this->assertDatabaseCount('deal_commissions', 0);
        $this->assertSame('draft', $agreement->fresh()->status);
    }

    public function test_approval_creates_integrity_checked_audit(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);
        $admin = $this->approver();

        $this->actingAs($admin);

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $audit = \App\Models\DealAudit::query()->sole();
        $commission = DealCommission::query()->sole();

        $this->assertSame('agreement.approved', $audit->event);
        $this->assertEquals($deal->id, $audit->deal_id);
        $this->assertEquals($admin->id, $audit->actor_id);

        $this->assertEquals(
            $agreement->id,
            $audit->payload['agreement_id']
        );

        $this->assertEquals(
            $commission->id,
            $audit->payload['commission_id']
        );

        $this->assertEquals(
            '125.00',
            $audit->payload['commission_amount']
        );

        $this->assertSame(
            $agreement->fresh()->content_sha256,
            $audit->payload['agreement_sha256']
        );

        $json = json_encode(
            $audit->payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $this->assertSame(
            hash('sha256', $json),
            $audit->payload_sha256
        );
    }

    public function test_duplicate_approval_does_not_duplicate_audit(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);

        $this->actingAs($this->approver());

        $this->approve($deal, $agreement)
            ->assertSessionHasNoErrors();

        $this->approve($deal, $agreement)
            ->assertSessionHasErrors('agreement');

        $this->assertDatabaseCount('deal_audits', 1);
        $this->assertDatabaseCount('deal_commissions', 1);
    }

    public function test_audit_failure_rolls_back_financial_approval(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);
        $admin = $this->approver();

        // This affects only the temporary SQLite test database.
        \Illuminate\Support\Facades\Schema::dropIfExists('deal_audits');

        try {
            app(\App\Services\Revenue\DealApprovalService::class)
                ->approve($deal, $agreement, $admin->id);

            $this->fail('Approval should fail without the audit table.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertNotEmpty($exception->getMessage());
        }

        $this->assertSame('draft', $deal->fresh()->status);
        $this->assertNull($deal->fresh()->approved_at);

        $this->assertSame('draft', $agreement->fresh()->status);
        $this->assertNull($agreement->fresh()->approved_at);
        $this->assertNull($agreement->fresh()->content_sha256);

        $this->assertDatabaseCount('deal_commissions', 0);
    }


    public function test_regular_admin_cannot_approve_financial_agreement(): void
    {
        $deal = $this->deal();
        $agreement = $this->agreement($deal);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $this->actingAs($admin);

        $this->approve($deal, $agreement)
            ->assertForbidden();

        $this->assertSame('draft', $deal->fresh()->status);
        $this->assertSame('draft', $agreement->fresh()->status);
        $this->assertDatabaseCount('deal_commissions', 0);
        $this->assertDatabaseCount('deal_audits', 0);
    }

}
