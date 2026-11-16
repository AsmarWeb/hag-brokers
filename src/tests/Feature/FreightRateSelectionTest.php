<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FreightRateSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        Notification::fake();
        $customer = Customer::create(['company_name' => 'Rate selection customer', 'is_active' => true]);
        $user = User::factory()->create(['customer_id' => $customer->id, 'email_verified_at' => now()]);
        $origin = Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE', 'is_active' => true]);
        $destination = Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA', 'is_active' => true]);
        $carrier = Carrier::create(['name' => 'Selection Line', 'code' => 'SEL-LINE', 'is_active' => true]);
        $source = FreightRateSource::create(['code' => 'selection-test', 'name' => 'HAG test rates', 'type' => 'manual', 'tier' => 'internal', 'is_active' => true]);
        $rate = FreightRate::create([
            'source_id' => $source->id,
            'carrier_id' => $carrier->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc',
            'min_quantity' => 1,
            'max_quantity' => 10,
            'supplier_unit_cost' => 1000,
            'customer_unit_price' => 1200,
            'currency' => 'USD',
            'surcharge_items' => [['label' => 'Documentation', 'supplier_amount' => 10, 'customer_amount' => 20]],
            'availability' => 'on_request',
            'booking_mode' => 'request',
            'rate_type' => 'manual_rate',
            'status' => 'published',
            'quoted_at' => now(),
            'expires_at' => now()->addDays(2),
        ]);

        return [$user, $rate];
    }

    public function test_customer_selecting_rate_creates_quotation_and_immutable_private_financial_snapshot(): void
    {
        [$user, $rate] = $this->fixture();
        $key = (string) Str::uuid();

        $response = $this->actingAs($user)->post(route('freight.rates.select', $rate), [
            'request_key' => $key,
            'quantity' => 2,
            'cargo_type' => 'General cargo',
            'customer_total' => 1,
            'supplier_total' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('quotations', 1);
        $this->assertDatabaseCount('freight_rate_bookings', 1);
        $this->assertDatabaseHas('freight_rate_bookings', [
            'customer_id' => $user->customer_id,
            'rate_id' => $rate->id,
            'status' => 'request_pending',
            'currency' => 'USD',
            'supplier_total' => '2010.00',
            'customer_total' => '2420.00',
            'commission_total' => '410.00',
        ]);
        $booking = \App\Models\FreightRateBooking::firstOrFail();
        $this->assertArrayNotHasKey('supplier_unit_cost', $booking->rate_snapshot);
        $this->assertArrayNotHasKey('supplier_total', $booking->toArray());
        $this->assertArrayNotHasKey('commission_total', $booking->toArray());
        $this->assertSame('2420.00', $booking->rate_snapshot['customer_total']);
    }

    public function test_same_idempotency_key_returns_same_request_without_duplicate_booking(): void
    {
        [$user, $rate] = $this->fixture();
        $payload = ['request_key' => (string) Str::uuid(), 'quantity' => 2, 'cargo_type' => 'General cargo'];

        $this->actingAs($user)->post(route('freight.rates.select', $rate), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('freight.rates.select', $rate), $payload)->assertRedirect();

        $this->assertDatabaseCount('quotations', 1);
        $this->assertDatabaseCount('freight_rate_bookings', 1);
    }

    public function test_unavailable_rate_is_rejected_and_guest_cannot_select(): void
    {
        [$user, $rate] = $this->fixture();
        $this->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(), 'quantity' => 1,
        ])->assertRedirect(route('login'));

        $rate->update(['expires_at' => now()->subMinute()]);
        $this->actingAs($user)->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(), 'quantity' => 1,
        ])->assertSessionHasErrors('rate');

        $this->assertDatabaseCount('quotations', 0);
        $this->assertDatabaseCount('freight_rate_bookings', 0);
    }
}
