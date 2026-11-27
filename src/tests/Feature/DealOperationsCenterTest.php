<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealAudit;
use App\Models\DealCommission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DealOperationsCenterTest extends TestCase
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

    private function customer(string $name = 'Maritime Operations Test LLC'): Customer
    {
        return Customer::create([
            'company_name' => $name,
            'contact_person' => 'Operations Contact',
            'country_code' => 'AE',
        ]);
    }

    private function deal(
        Customer $customer,
        string $status = 'draft',
        ?string $reference = null
    ): Deal {
        return Deal::create([
            'reference' => $reference ?? 'HAG-OPS-' . Str::upper(Str::random(12)),
            'customer_id' => $customer->id,
            'collection_model' => 'full_service',
            'status' => $status,
            'currency' => 'AED',
            'deal_amount' => '12500.00',
            'supplier_cost' => '11000.00',
            'platform_revenue' => '1500.00',
        ]);
    }

    public function test_admin_can_view_deal_center_with_status_statistics_and_filters(): void
    {
        $customer = $this->customer();
        $draft = $this->deal($customer, 'draft', 'HAG-OPS-DRAFT-001');
        $this->deal($customer, 'approved', 'HAG-OPS-APPROVED-001');

        $this->actingAs($this->admin())
            ->get(route('admin.deals.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee(__('deals.title'))
            ->assertSee(__('deals.total_deals'))
            ->assertSee(__('deals.approved_deals'))
            ->assertSee('HAG-OPS-DRAFT-001')
            ->assertDontSee('HAG-OPS-APPROVED-001')
            ->assertSee(route('admin.deals.show', $draft), false);
    }

    public function test_admin_can_search_deals_by_customer_name(): void
    {
        $matchingCustomer = $this->customer('Gulf Maritime Chartering');
        $otherCustomer = $this->customer('Different Trading Company');

        $this->deal($matchingCustomer, 'draft', 'HAG-OPS-GULF-001');
        $this->deal($otherCustomer, 'draft', 'HAG-OPS-OTHER-001');

        $this->actingAs($this->admin())
            ->get(route('admin.deals.index', ['search' => 'Gulf Maritime']))
            ->assertOk()
            ->assertSee('HAG-OPS-GULF-001')
            ->assertDontSee('HAG-OPS-OTHER-001');
    }

    public function test_admin_can_view_agreement_commission_snapshot_and_audit_trail(): void
    {
        $deal = $this->deal($this->customer(), 'approved', 'HAG-OPS-DETAIL-001');

        $agreement = $deal->agreements()->create([
            'version' => 1,
            'status' => 'approved',
            'fee_kind' => 'percent',
            'commission_bps' => 250,
            'terms' => 'Brokerage commission terms approved for this deal.',
            'content_sha256' => hash('sha256', 'approved agreement snapshot'),
            'approved_by' => $this->admin()->id,
            'approved_at' => now(),
            'integrity_version' => 2,
        ]);

        $commission = $deal->commissions()->create([
            'deal_agreement_id' => $agreement->id,
            'currency' => 'AED',
            'basis_amount' => '12500.00',
            'rate_bps' => 250,
            'amount' => '312.50',
            'status' => 'pending',
        ]);

        $payload = [
            'agreement_id' => $agreement->id,
            'commission_id' => $commission->id,
            'commission_amount' => '312.50',
            'currency' => 'AED',
        ];

        DealAudit::create([
            'deal_id' => $deal->id,
            'actor_id' => null,
            'event' => 'agreement.approved',
            'payload' => $payload,
            'payload_sha256' => hash(
                'sha256',
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            ),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.deals.show', $deal))
            ->assertOk()
            ->assertSee('HAG-OPS-DETAIL-001')
            ->assertSee(__('deals.financial_snapshot'))
            ->assertSee('312.50')
            ->assertSee('Brokerage commission terms approved for this deal.')
            ->assertSee(__('deals.audit_trail'));
    }

    public function test_guest_and_customer_cannot_access_deal_center(): void
    {
        $deal = $this->deal($this->customer());

        $this->get(route('admin.deals.index'))
            ->assertRedirect(route('login'));

        $customerUser = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customerUser)
            ->get(route('admin.deals.index'))
            ->assertForbidden();

        $this->actingAs($customerUser)
            ->get(route('admin.deals.show', $deal))
            ->assertForbidden();
    }
}
