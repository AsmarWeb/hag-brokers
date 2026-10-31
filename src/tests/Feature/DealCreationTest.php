<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\QuotationOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealCreationTest extends TestCase
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

    private function quotation(bool $approved = true): Quotation
    {
        $customer = Customer::create([
            'company_name' => 'HAG Deal Test',
            'country_code' => 'AE',
        ]);

        $origin = Port::create([
            'name' => 'Jebel Ali Test',
            'country_code' => 'AE',
        ]);

        $destination = Port::create([
            'name' => 'Destination Test',
            'country_code' => 'AE',
        ]);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
            'reference_no' => 'QT-DEAL-' . uniqid(),
        ]);

        if ($approved) {
            $carrier = Carrier::create([
                'name' => 'Test Carrier',
                'code' => 'TD-' . uniqid(),
                'is_active' => true,
            ]);

            $offer = QuotationOffer::create([
                'quotation_id' => $quotation->id,
                'carrier_id' => $carrier->id,
                'carrier_amount' => '1000.50',
                'customer_amount' => '1300.75',
                'currency' => 'USD',
                'status' => 'approved',
            ]);

            $quotation->forceFill([
                'approved_offer_id' => $offer->id,
                'approved_carrier_amount' => '1000.50',
                'approved_customer_amount' => '1300.75',
                'approved_margin' => '300.25',
                'approved_currency' => 'USD',
                'offer_approved_at' => now(),
            ])->save();
        }

        return $quotation;
    }

    public function test_admin_creates_draft_deal_from_approved_quotation(): void
    {
        $quotation = $this->quotation();

        $this->actingAs($this->admin())
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertRedirect(route('admin.quotations.show', $quotation))
            ->assertSessionHasNoErrors();

        $deal = Deal::query()->sole();

        $this->assertSame('draft', $deal->status);
        $this->assertSame('full_service', $deal->collection_model);
        $this->assertSame('USD', $deal->currency);
        $this->assertSame($quotation->customer_id, $deal->customer_id);
        $this->assertSame($quotation->id, $deal->quotation_id);
        $this->assertNull($deal->approved_at);
    }

    public function test_deal_uses_frozen_quotation_prices(): void
    {
        $quotation = $this->quotation();

        $this->actingAs($this->admin())
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertSessionHasNoErrors();

        $deal = Deal::query()->sole();

        $this->assertEquals('1300.75', $deal->deal_amount);
        $this->assertEquals('1000.50', $deal->supplier_cost);
        $this->assertEquals('300.25', $deal->platform_revenue);
    }

    public function test_duplicate_deal_is_rejected(): void
    {
        $quotation = $this->quotation();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertSessionHasErrors('quotation');

        $this->assertDatabaseCount('deals', 1);
    }

    public function test_unapproved_quotation_cannot_create_deal(): void
    {
        $quotation = $this->quotation(false);

        $this->actingAs($this->admin())
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertSessionHasErrors('quotation');

        $this->assertDatabaseCount('deals', 0);
    }

    public function test_customer_cannot_create_deal(): void
    {
        $quotation = $this->quotation();

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('admin.quotations.deals.store', $quotation))
            ->assertForbidden();

        $this->assertDatabaseCount('deals', 0);
    }

    public function test_guest_cannot_create_deal(): void
    {
        $quotation = $this->quotation();

        $this->post(route('admin.quotations.deals.store', $quotation))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('deals', 0);
    }
}
