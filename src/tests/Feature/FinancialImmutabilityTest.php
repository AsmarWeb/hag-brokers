<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealAudit;
use App\Models\DealCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class FinancialImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    private function records(): array
    {
        $customer = Customer::create([
            'company_name' => 'Financial Protection Test',
            'country_code' => 'AE',
        ]);

        $deal = Deal::create([
            'reference' => 'HAG-LOCK-' . Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => 'commission_only',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'platform_revenue' => '0.00',
        ]);

        $agreement = $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => 'percent',
            'commission_bps' => 250,
            'terms' => 'Financial protection test terms.',
        ]);

        $agreement->update(['status' => 'approved']);
        $deal->update(['status' => 'approved']);

        $commission = $deal->commissions()->create([
            'deal_agreement_id' => $agreement->id,
            'currency' => 'AED',
            'basis_amount' => '5000.00',
            'rate_bps' => 250,
            'amount' => '125.00',
            'status' => 'pending',
        ]);

        $audit = DealAudit::create([
            'deal_id' => $deal->id,
            'event' => 'agreement.approved',
            'payload' => ['agreement_id' => $agreement->id],
            'payload_sha256' => str_repeat('a', 64),
        ]);

        return compact('deal', 'agreement', 'commission', 'audit');
    }

    private function assertBlocked(callable $operation): void
    {
        try {
            $operation();
        } catch (LogicException $exception) {
            $this->assertSame(
                'Financial record is immutable.',
                $exception->getMessage()
            );
            return;
        }

        $this->fail('Financial modification should be blocked.');
    }

    public function test_approved_deal_cannot_be_modified_or_deleted(): void
    {
        ['deal' => $deal] = $this->records();

        $this->assertBlocked(
            fn () => $deal->update(['deal_amount' => '1.00'])
        );

        $this->assertBlocked(fn () => $deal->delete());

        $this->assertEquals('5000.00', $deal->fresh()->deal_amount);
    }

    public function test_approved_agreement_cannot_change(): void
    {
        ['agreement' => $agreement] = $this->records();

        $this->assertBlocked(
            fn () => $agreement->update(['commission_bps' => 9000])
        );

        $this->assertBlocked(fn () => $agreement->delete());

        $this->assertSame(
            250,
            $agreement->fresh()->commission_bps
        );
    }

    public function test_commission_cannot_be_modified_or_deleted(): void
    {
        ['commission' => $commission] = $this->records();

        $this->assertBlocked(
            fn () => $commission->update(['amount' => '999.00'])
        );

        $this->assertBlocked(fn () => $commission->delete());

        $this->assertEquals('125.00', $commission->fresh()->amount);
    }

    public function test_audit_cannot_be_modified_or_deleted(): void
    {
        ['audit' => $audit] = $this->records();

        $this->assertBlocked(
            fn () => $audit->update(['event' => 'changed'])
        );

        $this->assertBlocked(fn () => $audit->delete());

        $this->assertSame(
            'agreement.approved',
            $audit->fresh()->event
        );
    }
}
