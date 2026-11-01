<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminStabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']));
        return $admin;
    }

    private function payload(): array
    {
        return [
            'customer_id' => Customer::create(['company_name' => 'Stability Customer'])->id,
            'origin_port_id' => Port::create(['name' => 'Origin'])->id,
            'destination_port_id' => Port::create(['name' => 'Destination'])->id,
            'quantity' => 1,
            'status' => 'pending',
            'booking_no' => 'BOOK-'.uniqid(),
        ];
    }

    private function shipment(): Shipment
    {
        return Shipment::create($this->payload() + ['shipment_no' => 'SHP-'.uniqid()]);
    }

    public function test_all_registered_admin_controller_actions_exist(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (!str_starts_with($route->getName() ?? '', 'admin.')) continue;
            $action = $route->getActionName();
            if (!str_contains($action, '@')) continue;
            [$class, $method] = explode('@', $action, 2);
            $this->assertTrue(method_exists($class, $method), $route->getName().' points to missing '.$action);
            $this->assertTrue((new \ReflectionMethod($class, $method))->isPublic(), $action.' must be public');
        }
    }

    public function test_shipment_create_edit_and_show_pages_open_in_both_languages(): void
    {
        $admin = $this->admin();
        $shipment = $this->shipment();
        foreach (['ar', 'en'] as $locale) {
            $this->actingAs($admin)->withSession(['locale' => $locale]);
            $this->get(route('admin.shipments.create'))->assertOk();
            $this->get(route('admin.shipments.edit', $shipment))->assertOk();
            $this->get(route('admin.shipments.show', $shipment))->assertOk()->assertSee($shipment->shipment_no);
        }
    }

    public function test_shipment_creation_persists_business_links_and_schedule(): void
    {
        $payload = $this->payload();
        $quotation = Quotation::create([
            'customer_id' => $payload['customer_id'],
            'origin_port_id' => $payload['origin_port_id'],
            'destination_port_id' => $payload['destination_port_id'],
            'cargo_type' => 'General', 'container_type' => '40GP', 'reference_no' => 'QUOTE-LINK',
        ]);
        $carrier = Carrier::create(['name' => 'Test Carrier', 'code' => 'TEST-CARRIER']);
        $vessel = Vessel::create(['name' => 'Test Vessel', 'carrier_id' => $carrier->id]);
        $payload += [
            'quotation_id' => $quotation->id, 'carrier_id' => $carrier->id, 'vessel_id' => $vessel->id,
            'etd' => '2026-11-02T09:30', 'eta' => '2026-11-05T10:45',
            'actual_departure' => '2026-11-02T10:00', 'actual_arrival' => '2026-11-05T11:00',
        ];
        $this->actingAs($this->admin())->post(route('admin.shipments.store'), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();
        $shipment = Shipment::where('booking_no', $payload['booking_no'])->firstOrFail();
        $this->assertStringStartsWith('HAG-SHP-', $shipment->shipment_no);
        $this->assertEquals($quotation->id, $shipment->quotation->id);
        $this->assertEquals($carrier->id, $shipment->carrier->id);
        $this->assertEquals($carrier->id, $shipment->vessel->carrier->id);
        $this->assertSame('2026-11-02 09:30', $shipment->etd->format('Y-m-d H:i'));
        $this->assertSame('2026-11-05 10:45', $shipment->eta->format('Y-m-d H:i'));
        $this->assertSame('2026-11-02 10:00', $shipment->actual_departure->format('Y-m-d H:i'));
        $this->get(route('admin.shipments.edit', $shipment))->assertOk();
    }

    public function test_update_retains_shipment_number_and_allows_unchanged_booking_number(): void
    {
        $shipment = $this->shipment();
        $payload = $shipment->only(['customer_id', 'origin_port_id', 'destination_port_id', 'booking_no', 'quantity', 'status']);
        $payload['quantity'] = 3;
        $this->actingAs($this->admin())->put(route('admin.shipments.update', $shipment), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($shipment->shipment_no, $shipment->fresh()->shipment_no);
        $this->assertSame(3, $shipment->fresh()->quantity);
    }

    public function test_invalid_shipment_fields_are_rejected_without_creating_records(): void
    {
        $payload = $this->payload();
        $payload['destination_port_id'] = $payload['origin_port_id'];
        $payload['quantity'] = 0;
        $payload['status'] = 'invalid';
        $this->actingAs($this->admin())->post(route('admin.shipments.store'), $payload)
            ->assertSessionHasErrors(['destination_port_id', 'quantity', 'status']);
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_quotation_from_another_customer_is_rejected(): void
    {
        $payload = $this->payload();
        $other = Customer::create(['company_name' => 'Other customer']);
        $quotation = Quotation::create([
            'customer_id' => $other->id, 'origin_port_id' => $payload['origin_port_id'],
            'destination_port_id' => $payload['destination_port_id'],
            'cargo_type' => 'General', 'container_type' => '40GP', 'reference_no' => 'OTHER-QUOTE',
        ]);
        $this->actingAs($this->admin())->post(route('admin.shipments.store'), $payload + ['quotation_id' => $quotation->id])
            ->assertSessionHasErrors('quotation_id');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_arrival_before_departure_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.shipments.store'), $this->payload() + [
            'etd' => '2026-11-05T10:00', 'eta' => '2026-11-04T10:00',
        ])->assertSessionHasErrors('eta');
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_empty_shipment_can_be_deleted(): void
    {
        $shipment = $this->shipment();
        $this->actingAs($this->admin())->delete(route('admin.shipments.destroy', $shipment))
            ->assertRedirect(route('admin.shipments.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }

    public function test_shipment_with_container_cannot_be_deleted(): void
    {
        $shipment = $this->shipment();
        $shipment->containers()->create(['container_no' => 'HAG-CONTAINER-1']);
        $this->actingAs($this->admin())->delete(route('admin.shipments.destroy', $shipment))->assertSessionHas('error');
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
        $this->assertDatabaseHas('containers', ['shipment_id' => $shipment->id]);
    }

    public function test_shipment_with_tracking_event_cannot_be_deleted(): void
    {
        $shipment = $this->shipment();
        $shipment->events()->create(['event_type' => 'created', 'event_date' => now()]);
        $this->actingAs($this->admin())->delete(route('admin.shipments.destroy', $shipment))->assertSessionHas('error');
        $this->assertDatabaseHas('shipment_events', ['shipment_id' => $shipment->id]);
    }

    public function test_shipment_with_document_cannot_be_deleted(): void
    {
        $shipment = $this->shipment();
        Document::create(['shipment_id' => $shipment->id, 'name' => 'Business document', 'type' => 'other', 'file_path' => 'documents/test.pdf']);
        $this->actingAs($this->admin())->delete(route('admin.shipments.destroy', $shipment))->assertSessionHas('error');
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
        $this->assertDatabaseHas('documents', ['shipment_id' => $shipment->id]);
    }

    public function test_regular_user_cannot_create_update_or_delete_shipments(): void
    {
        $shipment = $this->shipment();
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.shipments.create'))->assertForbidden();
        $this->post(route('admin.shipments.store'), $this->payload())->assertForbidden();
        $this->put(route('admin.shipments.update', $shipment), [])->assertForbidden();
        $this->delete(route('admin.shipments.destroy', $shipment))->assertForbidden();
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
    }

    public function test_port_details_open_in_both_languages_and_missing_port_returns_404(): void
    {
        $port = Port::create(['name' => 'Jebel Ali Test']);
        $this->actingAs($this->admin());
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('admin.ports.show', $port))
                ->assertOk()->assertSee('Jebel Ali Test');
        }
        $this->get(route('admin.ports.show', 999999))->assertNotFound();
    }

    public function test_port_can_be_created_without_optional_country_code(): void
    {
        $this->actingAs($this->admin())->post(route('admin.ports.store'), ['name' => 'Minimal port'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ports', ['name' => 'Minimal port', 'country_code' => null]);
    }

    public function test_nonexistent_customer_assignment_is_a_validation_error(): void
    {
        $target = User::factory()->create();
        $this->actingAs($this->admin())->patch(route('admin.users.customer', $target), ['customer_id' => 999999])
            ->assertSessionHasErrors('customer_id');
        $this->assertNull($target->fresh()->customer_id);
    }

    public function test_dashboard_displays_actual_quotation_reference(): void
    {
        $shipment = $this->shipment();
        Quotation::create([
            'customer_id' => $shipment->customer_id, 'origin_port_id' => $shipment->origin_port_id,
            'destination_port_id' => $shipment->destination_port_id,
            'cargo_type' => 'General', 'container_type' => '40GP', 'reference_no' => 'HAG-ACTUAL-REFERENCE',
        ]);
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk()->assertSee('HAG-ACTUAL-REFERENCE');
    }

    public function test_adding_quotation_link_preserves_existing_shipment(): void
    {
        $migration = require database_path('migrations/2026_11_01_190000_add_quotation_link_to_shipments.php');
        // The shared TestCase requires SQLite :memory: before RefreshDatabase runs.
        $migration->down();
        $shipment = $this->shipment();
        $migration->up();
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'shipment_no' => $shipment->shipment_no, 'quotation_id' => null]);
    }
}
