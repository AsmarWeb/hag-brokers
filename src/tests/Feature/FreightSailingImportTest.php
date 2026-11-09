<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\FreightSailing;
use App\Models\Port;
use App\Models\Role;
use App\Models\User;
use App\Models\Vessel;
use App\Services\Freight\SailingImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class FreightSailingImportTest extends TestCase
{
    use RefreshDatabase;

    private function schedule(int $departureDays = 3): UploadedFile
    {
        $departure = now('Asia/Dubai')->addDays($departureDays);

        return UploadedFile::fake()->createWithContent('schedule.csv', implode(',', SailingImport::HEADERS)."\n".implode(',', [
            'HAGTEST', '1234567', 'AEJEA', 'SADMM', 'VOY-1',
            $departure->format('Y-m-d H:i'),
            $departure->copy()->addDay()->format('Y-m-d H:i'),
            $departure->copy()->subDay()->format('Y-m-d H:i'),
            '20ft|40ft_hc',
        ])."\n");
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $admin;
    }

    private function catalog(): Vessel
    {
        Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE', 'is_active' => true]);
        Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA', 'is_active' => true]);
        $carrier = Carrier::create(['name' => 'Test carrier', 'code' => 'HAGTEST', 'is_active' => true]);

        return Vessel::create(['name' => 'Test vessel', 'imo' => '1234567', 'carrier_id' => $carrier->id]);
    }

    public function test_admin_previews_and_publishes_catalog_linked_sailings_and_updates_dates(): void
    {
        $this->freezeTime();
        $vessel = $this->catalog();
        $preview = route('admin.freight-sailings.import.preview');
        $publish = route('admin.freight-sailings.import.publish');
        $this->actingAs(User::factory()->create())->postJson($preview, ['schedule' => $this->schedule()])->assertForbidden();

        $this->actingAs($this->admin())->get(route('admin.freight-sailings.index'))
            ->assertOk()->assertSee(route('admin.freight-sailings.import'));
        $this->get(route('admin.freight-sailings.import.template'))->assertOk()
            ->assertSee(implode(',', SailingImport::HEADERS));
        $new = $this->post($preview, ['schedule' => $this->schedule()])->assertOk()->assertViewHas('plan.0.action', 'new');
        $this->assertDatabaseCount('freight_sailings', 0);
        $this->post($publish, ['token' => $new->viewData('token')])->assertRedirect(route('admin.freight-sailings.index'));
        $sailing = FreightSailing::sole();
        $this->assertEquals($vessel->id, $sailing->vessel_id);
        $this->assertEquals($vessel->carrier_id, $sailing->carrier_id);
        $this->assertSame('AEJEA', $sailing->originPort->unlocode);
        $this->assertSame('SADMM', $sailing->destinationPort->unlocode);
        $this->getJson('/api/v1/freight/sailings')->assertOk()->assertJsonPath('data.0.vessel_name', $vessel->name);

        $this->get(route('admin.freight-sailings.index', ['q' => 'AEJEA', 'status' => 'published']))
            ->assertOk()->assertSee('VOY-1');
        $this->get(route('admin.freight-sailings.index', ['q' => 'AEJEA', 'status' => 'cancelled']))
            ->assertOk()->assertDontSee('VOY-1');
        $this->get(route('admin.freight-sailings.index', ['q' => 'NO-MATCH']))
            ->assertOk()->assertDontSee('VOY-1');

        $update = $this->post($preview, ['schedule' => $this->schedule(4)])->assertOk()->assertViewHas('plan.0.action', 'update');
        $this->assertSame($sailing->departure_at->timestamp, $sailing->fresh()->departure_at->timestamp);
        $this->post($publish, ['token' => $update->viewData('token')])->assertRedirect(route('admin.freight-sailings.index'));
        $this->assertDatabaseCount('freight_sailings', 1);
        $this->assertEquals($sailing->departure_at->addDay()->timestamp, $sailing->fresh()->departure_at->timestamp);
        $this->post($preview, ['schedule' => $this->schedule(4)])->assertOk()->assertViewHas('plan.0.action', 'unchanged');
    }

    public function test_stale_preview_and_mismatched_catalog_do_not_publish(): void
    {
        $vessel = $this->catalog();
        $this->actingAs($this->admin());
        $preview = route('admin.freight-sailings.import.preview');
        $publish = route('admin.freight-sailings.import.publish');

        $response = $this->post($preview, ['schedule' => $this->schedule()])->assertOk();
        $vessel->update(['name' => 'Renamed vessel']);
        $this->postJson($publish, ['token' => $response->viewData('token')])->assertUnprocessable()->assertJsonValidationErrors('schedule');
        $this->assertDatabaseCount('freight_sailings', 0);

        $vessel->update(['carrier_id' => null]);
        $this->postJson($preview, ['schedule' => $this->schedule()])->assertUnprocessable()->assertJsonValidationErrors('schedule');
        $this->assertDatabaseCount('freight_sailings', 0);
    }
}
