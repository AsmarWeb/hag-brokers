<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\Port;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdminFreightRateBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    private function submittedRequest(): array
    {
        $customer = Customer::create(['company_name' => 'Phase Five Customer', 'is_active' => true]);
        $user = User::factory()->create([
            'customer_id' => $customer->id,
            'email_verified_at' => now(),
        ]);
        $origin = Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE', 'is_active' => true]);
        $destination = Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA', 'is_active' => true]);
        $carrier = Carrier::create(['name' => 'Phase Five Carrier', 'code' => 'P5C', 'is_active' => true]);
        $source = FreightRateSource::create([
            'code' => 'phase-five-test',
            'name' => 'Phase Five test source',
            'type' => 'manual',
            'tier' => 'internal',
            'is_active' => true,
        ]);
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
            'availability' => 'on_request',
            'booking_mode' => 'request',
            'rate_type' => 'manual_rate',
            'status' => 'published',
            'quoted_at' => now(),
            'expires_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)->post(route('freight.rates.select', $rate), [
            'request_key' => (string) Str::uuid(),
            'quantity' => 1,
            'cargo_type' => 'General cargo',
        ])->assertRedirect();

        return [$user, \App\Models\FreightRateBooking::query()->firstOrFail()];
    }

    private function administrator(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->addRole(Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        ));

        return $admin;
    }

    public function test_regular_user_cannot_access_admin_booking_pages_or_change_a_booking(): void
    {
        [$user, $booking] = $this->submittedRequest();

        $this->actingAs($user)
            ->get(route('admin.freight-bookings.index'))
            ->assertForbidden();

        $this->get(route('admin.freight-bookings.show', $booking))
            ->assertForbidden();

        $this->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'mark_sent',
            'note' => 'Unauthorized attempt to forward the request.',
        ])->assertForbidden();

        $this->assertDatabaseHas('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'request_pending',
        ]);
        $this->assertDatabaseMissing('freight_rate_booking_events', [
            'freight_rate_booking_id' => $booking->id,
            'event_key' => 'marked_sent_to_carrier',
        ]);
    }

    public function test_administrator_can_open_booking_management_pages(): void
    {
        [, $booking] = $this->submittedRequest();
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->get(route('admin.freight-bookings.index'))
            ->assertOk();

        $this->get(route('admin.freight-bookings.show', $booking))
            ->assertOk();
    }

    public function test_admin_can_record_carrier_handoff_without_marking_booking_confirmed(): void
    {
        [, $booking] = $this->submittedRequest();
        $admin = $this->administrator();

        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'mark_sent',
            'note' => 'Sent by email to carrier booking desk; reference EMAIL-55.',
        ])->assertRedirect(route('admin.freight-bookings.show', $booking));

        $this->assertDatabaseHas('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'awaiting_carrier',
            'confirmed_at' => null,
        ]);
        $this->assertDatabaseHas('freight_rate_booking_events', [
            'freight_rate_booking_id' => $booking->id,
            'event_key' => 'marked_sent_to_carrier',
            'from_status' => 'request_pending',
            'to_status' => 'awaiting_carrier',
        ]);
    }

    public function test_admin_cannot_mark_request_confirmed_and_cannot_send_twice(): void
    {
        [, $booking] = $this->submittedRequest();
        $admin = $this->administrator();

        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'mark_sent',
            'note' => 'Sent to carrier by email.',
        ])->assertRedirect();

        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'mark_sent',
            'note' => 'Duplicate handoff.',
        ])->assertSessionHasErrors('action');

        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'confirm',
            'note' => 'Attempt to confirm without carrier evidence.',
        ])->assertSessionHasErrors('action');

        $this->assertDatabaseMissing('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'carrier_confirmed',
        ]);
    }

    public function test_admin_rejection_requires_a_reason_and_is_audited(): void
    {
        [, $booking] = $this->submittedRequest();
        $admin = $this->administrator();

        $this->actingAs($admin)->patch(route('admin.freight-bookings.status', $booking), [
            'action' => 'reject',
            'note' => 'The carrier rate expired before review.',
        ])->assertRedirect();

        $this->assertDatabaseHas('freight_rate_bookings', [
            'id' => $booking->id,
            'status' => 'admin_rejected',
        ]);
        $this->assertDatabaseHas('freight_rate_booking_events', [
            'freight_rate_booking_id' => $booking->id,
            'event_key' => 'admin_rejected',
            'to_status' => 'admin_rejected',
        ]);
    }
}
