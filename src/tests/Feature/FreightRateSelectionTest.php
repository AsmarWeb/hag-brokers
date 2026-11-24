<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateBooking;
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


    public function test_customer_can_view_safe_booking_timeline_without_internal_event_data(): void
    {
        [$user, $rate] = $this->fixture();

        $this->actingAs($user)->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(),
            'quantity' => 2,
            'cargo_type' => 'General cargo',
        ])->assertRedirect();

        $booking = FreightRateBooking::query()->firstOrFail();
        $adminActor = User::factory()->create(['name' => 'PRIVATE ADMIN ACTOR NAME']);

        $booking->events()->create([
            'actor_id' => $adminActor->id,
            'event_key' => 'marked_sent_to_carrier',
            'from_status' => 'request_pending',
            'to_status' => 'awaiting_carrier',
            'note' => 'PRIVATE ADMIN NOTE 8472',
            'metadata' => ['private_tracking_value' => 'PRIVATE METADATA 8472'],
        ]);

        $booking->events()->create([
            'actor_id' => null,
            'event_key' => 'carrier_confirmed',
            'from_status' => 'awaiting_carrier',
            'to_status' => 'carrier_confirmed',
            'note' => 'PRIVATE CARRIER NOTE 8472',
            'metadata' => ['carrier_internal_code' => 'PRIVATE CARRIER CODE 8472'],
        ]);

        $booking->forceFill([
            'status' => 'carrier_confirmed',
            'carrier_reference' => 'CARRIER-REF-PUBLIC-47',
            'status_note' => 'PRIVATE BOOKING STATUS NOTE 8472',
        ])->save();

        $quotation = $booking->quotation()->firstOrFail();

        $response = $this->actingAs($user)->get(route('quotations.show', $quotation));

        $response->assertOk()
            ->assertSee(__('freight_selection.carrier_confirmed'))
            ->assertSee(__('freight_selection.timeline'))
            ->assertSee(__('freight_selection.timeline_events.request_submitted'))
            ->assertSee(__('freight_selection.timeline_events.marked_sent_to_carrier'))
            ->assertSee(__('freight_selection.timeline_events.carrier_confirmed'))
            ->assertSee('CARRIER-REF-PUBLIC-47')
            ->assertDontSee('PRIVATE ADMIN ACTOR NAME')
            ->assertDontSee('PRIVATE ADMIN NOTE 8472')
            ->assertDontSee('PRIVATE METADATA 8472')
            ->assertDontSee('PRIVATE CARRIER NOTE 8472')
            ->assertDontSee('PRIVATE CARRIER CODE 8472')
            ->assertDontSee('PRIVATE BOOKING STATUS NOTE 8472')
            ->assertDontSee('HAG test rates')
            ->assertDontSee('supplier_total')
            ->assertDontSee('commission_total')
            ->assertDontSee('payload_hash');

        $html = $response->getContent();
        $submittedPosition = strpos($html, __('freight_selection.timeline_events.request_submitted'));
        $sentPosition = strpos($html, __('freight_selection.timeline_events.marked_sent_to_carrier'));
        $confirmedPosition = strpos($html, __('freight_selection.timeline_events.carrier_confirmed'));

        $this->assertNotFalse($submittedPosition);
        $this->assertNotFalse($sentPosition);
        $this->assertNotFalse($confirmedPosition);
        $this->assertLessThan($sentPosition, $submittedPosition);
        $this->assertLessThan($confirmedPosition, $sentPosition);

        $otherCustomer = Customer::create([
            'company_name' => 'Another freight customer',
            'is_active' => true,
        ]);
        $otherUser = User::factory()->create([
            'customer_id' => $otherCustomer->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($otherUser)
            ->get(route('quotations.show', $quotation))
            ->assertForbidden();
    }

    public function test_customer_quotation_list_displays_selected_freight_booking_status(): void
    {
        [$user, $rate] = $this->fixture();

        $this->actingAs($user)->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(),
            'quantity' => 1,
            'cargo_type' => 'General cargo',
        ])->assertRedirect();

        $this->actingAs($user)
            ->get(route('quotations.index'))
            ->assertOk()
            ->assertSee(__('freight_selection.request_pending'));
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
