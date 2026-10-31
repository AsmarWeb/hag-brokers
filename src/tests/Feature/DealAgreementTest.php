<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealAgreementTest extends TestCase
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

    private function deal(
        string $model = 'full_service',
        string $status = 'draft'
    ): Deal {
        $customer = Customer::create([
            'company_name' => 'HAG Agreement Test',
            'country_code' => 'AE',
        ]);

        return Deal::create([
            'reference' => 'HAG-TEST-' . Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => $model,
            'status' => $status,
            'currency' => 'AED',
            'deal_amount' => '5000.00',
            'supplier_cost' => $model === 'full_service'
                ? '4000.00'
                : null,
            'platform_revenue' => '0.00',
        ]);
    }

    private function payload(string $kind = 'percent'): array
    {
        return [
            'fee_kind' => $kind,
            'commission_bps' => 250,
            'fixed_fee' => '150.00',
            'terms' => 'Brokerage fee agreed by the parties.',
        ];
    }

    public function test_admin_can_create_percentage_agreement(): void
    {
        $deal = $this->deal('commission_only');

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload('percent')
            )
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $agreement = DealAgreement::query()->sole();

        $this->assertSame('draft', $agreement->status);
        $this->assertSame('percent', $agreement->fee_kind);
        $this->assertSame(250, $agreement->commission_bps);
        $this->assertSame(1, $agreement->version);
        $this->assertNull($agreement->approved_at);
        $this->assertDatabaseCount('deal_commissions', 0);
    }

    public function test_admin_can_create_fixed_fee_agreement(): void
    {
        $deal = $this->deal('commission_only');

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload('fixed')
            )
            ->assertSessionHasNoErrors();

        $agreement = DealAgreement::query()->sole();

        $this->assertSame('fixed', $agreement->fee_kind);
        $this->assertEquals('150.00', $agreement->fixed_fee);
        $this->assertNull($agreement->commission_bps);
    }

    public function test_full_service_deal_can_use_spread(): void
    {
        $deal = $this->deal('full_service');

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload('spread')
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('deal_agreements', [
            'deal_id' => $deal->id,
            'fee_kind' => 'spread',
            'status' => 'draft',
        ]);
    }

    public function test_commission_only_deal_cannot_use_spread(): void
    {
        $deal = $this->deal('commission_only');

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload('spread')
            )
            ->assertSessionHasErrors('fee_kind');

        $this->assertDatabaseCount('deal_agreements', 0);
    }

    public function test_duplicate_draft_is_rejected(): void
    {
        $deal = $this->deal();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload()
            )
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload()
            )
            ->assertSessionHasErrors('agreement');

        $this->assertDatabaseCount('deal_agreements', 1);
    }

    public function test_non_draft_deal_rejects_agreement(): void
    {
        $deal = $this->deal('full_service', 'approved');

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload()
            )
            ->assertSessionHasErrors('deal');

        $this->assertDatabaseCount('deal_agreements', 0);
    }

    public function test_customer_cannot_create_agreement(): void
    {
        $deal = $this->deal();

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(
                route('admin.deals.agreements.store', $deal),
                $this->payload()
            )
            ->assertForbidden();

        $this->assertDatabaseCount('deal_agreements', 0);
    }

    public function test_guest_and_invalid_terms_are_rejected(): void
    {
        $deal = $this->deal();

        $this->post(
            route('admin.deals.agreements.store', $deal),
            $this->payload()
        )->assertRedirect(route('login'));

        $payload = $this->payload();
        $payload['terms'] = 'short';

        $this->actingAs($this->admin())
            ->post(
                route('admin.deals.agreements.store', $deal),
                $payload
            )
            ->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('deal_agreements', 0);
    }
}
