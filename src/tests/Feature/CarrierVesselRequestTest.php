<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\CarrierVesselRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarrierVesselRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_carrier_requests_create_or_update_vessels_only_after_admin_approval(): void
    {
        $carrier = Carrier::create(['name' => 'Carrier', 'code' => 'PARTNER', 'is_active' => true]);
        $other = Carrier::create(['name' => 'Other', 'code' => 'OTHER', 'is_active' => true]);
        $partner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin']));
        $invitation = CarrierInvitation::create([
            'carrier_id' => $carrier->id, 'email' => $partner->email,
            'token_hash' => hash('sha256', 'fixture'), 'expires_at' => now()->addDay(),
        ]);
        $invitation->forceFill(['user_id' => $partner->id, 'accepted_at' => now()])->save();

        $submit = route('carrier.fleet.requests.store');
        $payload = ['name' => 'Partner vessel', 'imo' => '1234567', 'vessel_type' => 'Container', 'carrier_id' => $other->id];
        $this->actingAs($partner)->post($submit, $payload)->assertRedirect(route('carrier.fleet'));
        $proposal = CarrierVesselRequest::sole();
        $this->assertEquals($carrier->id, $proposal->carrier_id);
        $this->assertDatabaseCount('vessels', 0);
        $this->postJson($submit, $payload)->assertUnprocessable()->assertJsonValidationErrors('imo');
        $review = route('admin.vessels.partner-requests.review', $proposal);
        $this->patchJson($review, ['decision' => 'approve'])->assertForbidden();

        $this->actingAs($admin)->get(route('admin.vessels.partner-requests.index'))->assertOk()->assertSee('Partner vessel');
        $this->patch($review, ['decision' => 'approve'])->assertRedirect();
        $vessel = Vessel::sole();
        $this->assertEquals($carrier->id, $vessel->carrier_id);
        $this->patchJson($review, ['decision' => 'approve'])->assertConflict();

        $this->actingAs($partner)->post($submit, array_replace($payload, ['name' => 'Updated vessel']))->assertRedirect();
        $update = CarrierVesselRequest::latest('id')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.vessels.partner-requests.review', $update), ['decision' => 'approve'])->assertRedirect();
        $this->assertDatabaseCount('vessels', 1);
        $this->assertSame('Updated vessel', $vessel->fresh()->name);

        $foreign = Vessel::create(['name' => 'Foreign vessel', 'imo' => '7654321', 'carrier_id' => $other->id]);
        $this->actingAs($partner)->post($submit, array_replace($payload, ['imo' => $foreign->imo]))->assertRedirect();
        $conflict = CarrierVesselRequest::latest('id')->firstOrFail();
        $decision = route('admin.vessels.partner-requests.review', $conflict);
        $this->actingAs($admin)->patchJson($decision, ['decision' => 'approve'])->assertUnprocessable();
        $this->patch($decision, ['decision' => 'reject', 'review_note' => 'IMO assigned to another carrier'])->assertRedirect();
        $this->assertEquals($other->id, $foreign->fresh()->carrier_id);
        $this->actingAs($partner)->get(route('carrier.fleet'))->assertOk()
            ->assertSee('IMO assigned to another carrier')->assertDontSee('Foreign vessel')
            ->assertSee('id="carrier-portal"', false)->assertSee('href="#carrier-vessel-requests"', false)
            ->assertDontSee('href="'.route('dashboard').'"', false);
        $this->get(route('carrier.fleet', ['lang' => 'ar']))->assertOk()
            ->assertSee('dir="rtl"', false)->assertSee(__('carrier_partner.portal'));
    }
}
