<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\QuotationOffer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuotationOfferApprovalTest extends TestCase
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

    private function prepareOffer(array $attributes = []): array
    {
        $customer = Customer::create([
            'company_name' => 'Approval Test',
            'country_code' => 'AE',
        ]);

        $origin = Port::create([
            'name' => 'Approval Origin',
            'country_code' => 'AE',
        ]);

        $destination = Port::create([
            'name' => 'Approval Destination',
            'country_code' => 'AE',
        ]);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
            'reference_no' => 'QT-AP-' . uniqid(),
        ]);

        $carrier = Carrier::create([
            'name' => 'Approval Carrier',
            'code' => 'AC-' . uniqid(),
            'is_active' => true,
        ]);

        $offer = QuotationOffer::create(array_merge([
            'quotation_id' => $quotation->id,
            'carrier_id' => $carrier->id,
            'carrier_amount' => '1000.50',
            'customer_amount' => '1300.75',
            'currency' => 'USD',
            'status' => 'pending',
        ], $attributes));

        return [$quotation, $offer];
    }

    public function test_admin_approves_offer_and_freezes_prices(): void
    {
        [$quotation, $offer] = $this->prepareOffer();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.offers.approve', [$quotation, $offer]))
            ->assertRedirect(route('admin.quotations.show', $quotation))
            ->assertSessionHasNoErrors();

        $quotation->refresh();
        $offer->refresh();

        $this->assertSame('approved', $offer->status);
        $this->assertEquals($offer->id, $quotation->approved_offer_id);
        $this->assertEquals('1000.50', $quotation->approved_carrier_amount);
        $this->assertEquals('1300.75', $quotation->approved_customer_amount);
        $this->assertEquals('300.25', $quotation->approved_margin);
        $this->assertSame('USD', $quotation->approved_currency);
        $this->assertEquals($admin->id, $quotation->offer_approved_by);
        $this->assertNotNull($quotation->offer_approved_at);

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'event' => 'offer_approved',
            'new_value' => (string) $offer->id,
        ]);
    }

    public function test_second_approval_is_rejected(): void
    {
        [$quotation, $first] = $this->prepareOffer();

        $second = QuotationOffer::create([
            'quotation_id' => $quotation->id,
            'carrier_id' => $first->carrier_id,
            'carrier_amount' => '900.00',
            'customer_amount' => '1200.00',
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.offers.approve', [$quotation, $first]))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.offers.approve', [$quotation, $second]))
            ->assertSessionHasErrors('offer');

        $this->assertEquals($first->id, $quotation->fresh()->approved_offer_id);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame(
            1,
            DB::table('quotation_history')->where('event', 'offer_approved')->count()
        );
    }

    public function test_expired_offer_cannot_be_approved(): void
    {
        [$quotation, $offer] = $this->prepareOffer([
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.quotations.offers.approve', [$quotation, $offer]))
            ->assertSessionHasErrors('offer');

        $this->assertNull($quotation->fresh()->approved_offer_id);
    }

    public function test_offer_from_different_quotation_is_rejected(): void
    {
        [$firstQuotation] = $this->prepareOffer();
        [, $differentOffer] = $this->prepareOffer();

        $this->actingAs($this->admin())
            ->patch(route('admin.quotations.offers.approve', [
                $firstQuotation,
                $differentOffer,
            ]))
            ->assertNotFound();

        $this->assertNull($firstQuotation->fresh()->approved_offer_id);
    }

    public function test_customer_cannot_approve_offer(): void
    {
        [$quotation, $offer] = $this->prepareOffer();

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->patch(route('admin.quotations.offers.approve', [$quotation, $offer]))
            ->assertForbidden();

        $this->assertNull($quotation->fresh()->approved_offer_id);
    }

    public function test_approved_quotation_rejects_new_offers(): void
    {
        [$quotation, $offer] = $this->prepareOffer();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.offers.approve', [$quotation, $offer]))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.quotations.offers.store', $quotation), [
                'carrier_id' => $offer->carrier_id,
                'carrier_amount' => 800,
                'customer_amount' => 1000,
                'currency' => 'USD',
            ])
            ->assertSessionHasErrors('offer');

        $this->assertDatabaseCount('quotation_offers', 1);
    }
}
