<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateBooking;
use App\Models\FreightRateSource;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CarrierFreightRateBookingTest extends TestCase
{
    use RefreshDatabase;

    private function submittedRequest(): array
    {
        $customer = Customer::create(['company_name' => 'Phase Six Customer', 'is_active' => true]);
        $customerUser = User::factory()->create(['customer_id' => $customer->id, 'email_verified_at' => now()]);
        $origin = Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE', 'is_active' => true]);
        $destination = Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA', 'is_active' => true]);
        $carrier = Carrier::create(['name' => 'Phase Six Carrier', 'code' => 'P6C', 'is_active' => true]);
        $source = FreightRateSource::create([
            'code' => 'phase-six-test', 'name' => 'Phase Six test source', 'type' => 'manual',
            'tier' => 'internal', 'is_active' => true,
        ]);
        $rate = FreightRate::create([
            'source_id' => $source->id, 'carrier_id' => $carrier->id,
            'origin_port_id' => $origin->id, 'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc', 'min_quantity' => 1, 'max_quantity' => 10,
            'supplier_unit_cost' => 1000, 'customer_unit_price' => 1200, 'currency' => 'USD',
            'availability' => 'on_request', 'booking_mode' => 'request', 'rate_type' => 'manual_rate',
            'status' => 'published', 'quoted_at' => now(), 'expires_at' => now()->addDays(2),
        ]);

        $this->actingAs($customerUser)->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(), 'quantity' => 1, 'cargo_type' => 'General cargo',
        ])->assertRedirect();

        $booking = FreightRateBooking::query()->firstOrFail();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $this->withoutMiddleware(\Laratrust\Middleware\Role::class);
        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'mark_sent', 'note' => 'Forwarded to carrier operations.',
        ])->assertRedirect();

        return [$carrier, $booking];
    }

    private function representative(Carrier $carrier): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $invitation = CarrierInvitation::create([
            'carrier_id' => $carrier->id,
            'email' => $user->email,
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addDay(),
        ]);
        $invitation->forceFill(['user_id' => $user->id, 'accepted_at' => now()])->save();

        return $user;
    }

    public function test_only_assigned_carrier_can_list_and_view_its_forwarded_requests(): void
    {
        [$carrier, $booking] = $this->submittedRequest();
        $representative = $this->representative($carrier);
        $otherCarrier = Carrier::create(['name' => 'Other Carrier', 'code' => 'OTHER6', 'is_active' => true]);
        $otherRepresentative = $this->representative($otherCarrier);

        $this->actingAs($representative)
            ->get(route('carrier.freight-bookings.index'))
            ->assertOk()
            ->assertSee('Phase Six Carrier')
            ->assertSee((string) $booking->id);

        $this->actingAs($representative)
            ->get(route('carrier.freight-bookings.show', $booking->id))
            ->assertOk();

        $this->actingAs($otherRepresentative)
            ->get(route('carrier.freight-bookings.show', $booking->id))
            ->assertNotFound();

        $this->actingAs($otherRepresentative)
            ->get(route('carrier.freight-bookings.index'))
            ->assertOk()
            ->assertSee(__('carrier_freight_bookings.empty'))
            ->assertDontSee(__('carrier_freight_bookings.request_number', [
                'id' => $booking->id,
            ]));
    }

    public function test_unassociated_user_cannot_access_carrier_booking_portal(): void
    {
        [$carrier] = $this->submittedRequest();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->get(route('carrier.freight-bookings.index'))->assertForbidden();
    }

    public function test_carrier_can_confirm_with_reference_and_event_is_audited_without_financial_leak(): void
    {
        [$carrier, $booking] = $this->submittedRequest();
        $representative = $this->representative($carrier);

        $this->actingAs($representative)
            ->get(route('carrier.freight-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('1,200.00')
            ->assertDontSee('supplier_total')
            ->assertDontSee('commission_total')
            ->assertDontSee('payload_hash')
            ->assertDontSee('Phase Six test source');

        $this->actingAs($representative)->patch(route('carrier.freight-bookings.decision', $booking->id), [
            'decision' => 'accept',
            'carrier_reference' => 'CARRIER-REF-6001',
        ])->assertRedirect(route('carrier.freight-bookings.show', $booking->id));

        $this->assertDatabaseHas('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'carrier_confirmed',
            'carrier_reference' => 'CARRIER-REF-6001',
        ]);
        $this->assertNotNull($booking->fresh()->confirmed_at);
        $this->assertDatabaseHas('freight_rate_booking_events', [
            'freight_rate_booking_id' => $booking->id,
            'actor_id' => $representative->id,
            'event_key' => 'carrier_confirmed',
            'from_status' => 'awaiting_carrier',
            'to_status' => 'carrier_confirmed',
        ]);

        $this->actingAs($representative)->patch(route('carrier.freight-bookings.decision', $booking->id), [
            'decision' => 'reject', 'note' => 'Duplicate decision attempt.',
        ])->assertSessionHasErrors('decision');
    }

    public function test_carrier_rejection_requires_reason_and_is_audited(): void
    {
        [$carrier, $booking] = $this->submittedRequest();
        $representative = $this->representative($carrier);

        $this->actingAs($representative)->patch(route('carrier.freight-bookings.decision', $booking->id), [
            'decision' => 'reject',
        ])->assertSessionHasErrors('note');

        $this->actingAs($representative)->patch(route('carrier.freight-bookings.decision', $booking->id), [
            'decision' => 'reject',
            'note' => 'No equipment available for the requested departure.',
        ])->assertRedirect();

        $this->assertDatabaseHas('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'carrier_rejected',
            'status_note' => 'No equipment available for the requested departure.',
        ]);
        $this->assertNotNull($booking->fresh()->rejected_at);
        $this->assertDatabaseHas('freight_rate_booking_events', [
            'freight_rate_booking_id' => $booking->id,
            'actor_id' => $representative->id,
            'event_key' => 'carrier_rejected',
            'from_status' => 'awaiting_carrier',
            'to_status' => 'carrier_rejected',
        ]);
    }
}
