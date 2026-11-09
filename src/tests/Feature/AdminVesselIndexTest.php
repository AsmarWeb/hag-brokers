<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Role;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminVesselIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_vessel_list_renders_stored_eta_and_accepts_missing_dates(): void
    {
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $vessel = Vessel::create(['name' => 'Vessel with ETA']);
        $vessel->getConnection()->table('vessels')->where('id', $vessel->id)->update([
            'eta' => '2026-11-22 08:00:00',
            'last_position_at' => '2026-11-21 07:30:00',
        ]);
        Vessel::create(['name' => 'Vessel without ETA']);

        $this->actingAs($admin)->get(route('admin.vessels.index'))
            ->assertOk()->assertSee('22 Nov 2026 08:00')->assertSee('Vessel without ETA');

        $this->assertSame('2026-11-21 07:30', $vessel->fresh()->last_position_at->format('Y-m-d H:i'));
    }

    public function test_admin_vessel_details_render_with_and_without_a_carrier(): void
    {
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $carrier = Carrier::create(['name' => 'Details carrier', 'code' => 'DETAILS']);
        $vessel = Vessel::create([
            'name' => 'Vessel with details',
            'carrier_id' => $carrier->id,
            'eta' => '2026-11-22 08:00:00',
            'last_position_at' => '2026-11-21 07:30:00',
        ]);
        $unassigned = Vessel::create(['name' => 'Unassigned vessel']);

        $this->actingAs($admin)->get(route('admin.vessels.show', $vessel))
            ->assertOk()->assertSee($carrier->name)->assertSee('21 Nov 2026 07:30');
        $this->get(route('admin.vessels.show', $unassigned))
            ->assertOk()->assertSee('Not assigned');
    }
}
