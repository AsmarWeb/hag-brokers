<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealAudit;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealIntegrityVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealIntegrityVerifierTest extends TestCase
{
    use RefreshDatabase;

    private function approvedDeal(
        string $feeKind = 'percent'
    ): Deal {
        $user = User::factory()->create([
            'role' => 'super-admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        $customer = Customer::create([
            'company_name' => 'Integrity Verification Test',
            'country_code' => 'AE',
        ]);

        $deal = Deal::create([
            'reference' => 'HAG-VERIFY-' .
                Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => 'full_service',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => '4000.00',
            'platform_revenue' => '0.00',
        ]);

        $agreement = $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => $feeKind,
            'commission_bps' => $feeKind === 'percent'
                ? 250 : null,
            'fixed_fee' => $feeKind === 'fixed'
                ? '175.00' : null,
            'terms' => 'Approved brokerage agreement.',
        ]);

        $this->actingAs($user)
            ->patch(route(
                'admin.deals.agreements.approve',
                [$deal, $agreement]
            ))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            $deal->commissions()->count()
        );

        $this->assertSame(
            1,
            DealAudit::query()
                ->where('deal_id', $deal->id)
                ->count()
        );

        return $deal->fresh();
    }

    private function verify(Deal $deal): array
    {
        return app(DealIntegrityVerifier::class)
            ->verify($deal);
    }

    private function assertIssue(
        Deal $deal,
        string $issue
    ): void {
        $result = $this->verify($deal);

        $this->assertFalse($result['valid']);
        $this->assertContains(
            $issue,
            $result['issues']
        );
    }

    public function test_valid_approved_deal_passes(): void
    {
        foreach (['percent', 'fixed', 'spread'] as $kind) {
            // Separate approvals within one test database.
            $deal = $this->approvedDeal($kind);

            $result = $this->verify($deal);

            $this->assertTrue(
                $result['valid'],
                $kind . ': ' . implode(', ', $result['issues'])
            );

            $this->assertSame([], $result['issues']);
        }
    }

    public function test_direct_amount_tampering_is_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deals')
            ->where('id', $deal->id)
            ->update(['deal_amount' => '9000.00']);

        $this->assertIssue(
            $deal,
            'agreement_hash_mismatch'
        );
    }

    public function test_agreement_terms_tampering_is_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deal_agreements')
            ->where('deal_id', $deal->id)
            ->update(['terms' => 'Unauthorized replacement.']);

        $this->assertIssue(
            $deal,
            'agreement_hash_mismatch'
        );
    }

    public function test_commission_amount_tampering_is_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deal_commissions')
            ->where('deal_id', $deal->id)
            ->update(['amount' => '999.00']);

        $this->assertIssue(
            $deal,
            'commission_snapshot_mismatch'
        );
    }

    public function test_audit_payload_tampering_is_detected(): void
    {
        $deal = $this->approvedDeal();

        $audit = DealAudit::query()
            ->where('deal_id', $deal->id)
            ->sole();

        $payload = $audit->payload;
        $payload['commission_amount'] = '9999.00';

        // Deliberately bypass Eloquent protection in test DB.
        DB::table('deal_audits')
            ->where('id', $audit->id)
            ->update([
                'payload' => json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE |
                    JSON_THROW_ON_ERROR
                ),
            ]);

        $this->assertIssue(
            $deal,
            'audit_hash_mismatch'
        );
    }

    public function test_deleted_audit_is_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deal_audits')
            ->where('deal_id', $deal->id)
            ->delete();

        $this->assertIssue(
            $deal,
            'approval_audit_count_invalid'
        );
    }

    public function test_missing_commission_is_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deal_commissions')
            ->where('deal_id', $deal->id)
            ->delete();

        $this->assertIssue(
            $deal,
            'commission_count_invalid'
        );
    }

    public function test_unhashed_platform_revenue_change_is_not_detected(): void
    {
        $deal = $this->approvedDeal();

        DB::table('deals')
            ->where('id', $deal->id)
            ->update(['platform_revenue' => '9999.00']);

        // Documents a known limitation of the original hash.
        $result = $this->verify($deal);

        $this->assertTrue(
            $result['valid'],
            implode(', ', $result['issues'])
        );
    }
}
