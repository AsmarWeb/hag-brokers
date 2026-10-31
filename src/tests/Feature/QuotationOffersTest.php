<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationOffersTest extends TestCase
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

    private function quotation(): Quotation
    {
        $customer = Customer::create([
            'company_name' => 'Carrier Offer Test',
            'country_code' => 'AE',
        ]);

        $origin = Port::create([
            'name' => 'Origin Offer Test',
            'country_code' => 'AE',
        ]);

        $destination = Port::create([
            'name' => 'Destination Offer Test',
            'country_code' => 'AE',
        ]);

        return Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
            'reference_no' => 'QT-OFFER-' . uniqid(),
        ]);
    }

    private function carrier(bool $active = true): Carrier
    {
        return Carrier::create([
            'name' => 'Test Carrier',
            'code' => 'TC-' . uniqid(),
            'is_active' => $active,
        ]);
    }

    public function test_admin_creates_carrier_offer_and_audit_history(): void
    {
        $admin = $this->admin();
        $quotation = $this->quotation();
        $carrier = $this->carrier();

        $this->actingAs($admin)
            ->post(route('admin.quotations.offers.store', $quotation), [
                'carrier_id' => $carrier->id,
                'carrier_amount' => 1000,
                'customer_amount' => 1200,
                'currency' => 'USD',
            ])
            ->assertRedirect(route('admin.quotations.show', $quotation))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('quotation_offers', [
            'quotation_id' => $quotation->id,
            'carrier_id' => $carrier->id,
            'carrier_amount' => 1000,
            'customer_amount' => 1200,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'event' => 'offer_created',
            'user_id' => $admin->id,
        ]);
    }

    public function test_offer_below_carrier_cost_is_rejected(): void
    {
        $admin = $this->admin();
        $quotation = $this->quotation();
        $carrier = $this->carrier();

        $this->actingAs($admin)
            ->post(route('admin.quotations.offers.store', $quotation), [
                'carrier_id' => $carrier->id,
                'carrier_amount' => 1200,
                'customer_amount' => 900,
                'currency' => 'USD',
            ])
            ->assertSessionHasErrors('customer_amount');

        $this->assertDatabaseCount('quotation_offers', 0);
    }

    public function test_inactive_carrier_is_rejected(): void
    {
        $admin = $this->admin();
        $quotation = $this->quotation();
        $carrier = $this->carrier(false);

        $this->actingAs($admin)
            ->post(route('admin.quotations.offers.store', $quotation), [
                'carrier_id' => $carrier->id,
                'carrier_amount' => 1000,
                'customer_amount' => 1100,
                'currency' => 'USD',
            ])
            ->assertSessionHasErrors('carrier_id');

        $this->assertDatabaseCount('quotation_offers', 0);
    }

    public function test_customer_cannot_create_carrier_offer(): void
    {
        $quotation = $this->quotation();
        $carrier = $this->carrier();

        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.quotations.offers.store', $quotation), [
                'carrier_id' => $carrier->id,
                'carrier_amount' => 1000,
                'customer_amount' => 1200,
                'currency' => 'USD',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('quotation_offers', 0);
    }
}
