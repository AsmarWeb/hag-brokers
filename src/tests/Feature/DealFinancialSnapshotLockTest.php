<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class DealFinancialSnapshotLockTest extends TestCase
{
    use RefreshDatabase;

    private function deal(): Deal
    {
        $customer = Customer::create([
            'company_name' => 'Snapshot Lock Test',
            'country_code' => 'AE',
        ]);

        return Deal::create([
            'reference' => 'HAG-SNAP-' . Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => 'full_service',
            'status' => 'draft',
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => '4000.00',
            'platform_revenue' => '1000.00',
        ]);
    }

    private function addAgreement(Deal $deal): void
    {
        $deal->agreements()->create([
            'version' => 1,
            'status' => 'draft',
            'fee_kind' => 'spread',
            'terms' => 'The agreed brokerage and freight terms.',
        ]);
    }

    public function test_draft_amount_can_change_before_agreement(): void
    {
        $deal = $this->deal();

        $deal->update(['deal_amount' => '5500.00']);

        $this->assertEquals(
            '5500.00',
            $deal->fresh()->deal_amount
        );
    }

    public function test_amount_cannot_change_after_agreement(): void
    {
        $deal = $this->deal();
        $this->addAgreement($deal);

        $this->expectException(LogicException::class);

        $deal->update(['deal_amount' => '9000.00']);
    }

    public function test_collection_model_cannot_change_after_agreement(): void
    {
        $deal = $this->deal();
        $this->addAgreement($deal);

        $this->expectException(LogicException::class);

        $deal->update(['collection_model' => 'commission_only']);
    }

    public function test_approval_status_transition_remains_possible(): void
    {
        $deal = $this->deal();
        $this->addAgreement($deal);

        $deal->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->assertSame('approved', $deal->fresh()->status);
        $this->assertNotNull($deal->fresh()->approved_at);
    }
}
