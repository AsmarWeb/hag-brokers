<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\Port;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminFreightRateManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $user = User::factory()->create(['customer_id' => Customer::create(['company_name' => 'Admin workspace'])->id]);
        $user->addRole($role);
        return $user;
    }

    public function test_admin_source_credentials_are_encrypted_and_not_rendered(): void
    {
        $this->actingAs($this->admin())->post(route('admin.freight-rates.sources.store'), [
            'code' => 'test-source', 'name' => 'Test API', 'type' => 'provider', 'tier' => 'trial',
            'markup_percent' => 10, 'markup_fixed' => 5, 'capabilities' => ['rates'],
            'credentials_json' => '{"api_key":"secret-value"}', 'is_active' => 1,
        ])->assertRedirect();
        $source = FreightRateSource::firstOrFail();
        $this->assertSame(['api_key' => 'secret-value'], $source->credentials);
        $this->assertStringNotContainsString('secret-value', $source->getRawOriginal('credentials'));
        $this->actingAs($this->admin())->get(route('admin.freight-rates.index'))->assertOk()->assertDontSee('secret-value');
    }

    public function test_supplier_cost_and_markup_determine_customer_price(): void
    {
        $this->actingAs($this->admin());
        $source = FreightRateSource::create(['code' => 'hag-manual', 'name' => 'HAG Manual', 'type' => 'manual', 'tier' => 'internal', 'markup_percent' => 10, 'markup_fixed' => 5, 'is_active' => true]);
        $origin = Port::firstOrCreate(['unlocode' => 'AEJEA'], ['name' => 'Jebel Ali', 'country_code' => 'AE']);
        $destination = Port::firstOrCreate(['unlocode' => 'SADMM'], ['name' => 'Dammam', 'country_code' => 'SA']);
        $this->post(route('admin.freight-rates.store'), [
            'source_id' => $source->id, 'origin_port_id' => $origin->id, 'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc', 'min_quantity' => 1, 'supplier_unit_cost' => 1000,
            'currency' => 'USD', 'availability' => 'on_request', 'booking_mode' => 'request',
            'rate_type' => 'manual_rate', 'status' => 'published', 'customer_unit_price' => 1,
        ])->assertRedirect(route('admin.freight-rates.index'));
        $rate = FreightRate::firstOrFail();
        $this->assertSame('1105.00', $rate->customer_unit_price);
        $this->assertSame('1000.00', $rate->supplier_unit_cost);
    }
}
