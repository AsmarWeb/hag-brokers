<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FreightRateSearchTest extends TestCase
{
    use RefreshDatabase;

    private function rate(array $overrides = []): FreightRate
    {
        $origin = Port::firstOrCreate(['unlocode' => 'AEJEA'], ['name' => 'Jebel Ali', 'country_code' => 'AE']);
        $destination = Port::firstOrCreate(['unlocode' => 'SADMM'], ['name' => 'Dammam', 'country_code' => 'SA']);
        $carrier = Carrier::firstOrCreate(['code' => 'RATE-TEST'], ['name' => 'Rate Test Line', 'is_active' => true]);
        $source = FreightRateSource::firstOrCreate(['code' => 'test-manual'], [
            'name' => 'HAG test rates', 'type' => 'manual', 'tier' => 'internal', 'is_active' => true,
        ]);

        return FreightRate::create(array_replace([
            'source_id' => $source->id,
            'carrier_id' => $carrier->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc',
            'min_quantity' => 1,
            'supplier_unit_cost' => 1000,
            'customer_unit_price' => 1150,
            'currency' => 'USD',
            'surcharge_items' => [['code' => 'DOC', 'label' => 'Documentation', 'customer_amount' => 50]],
            'included_charges' => ['Origin THC'],
            'excluded_charges' => ['Destination customs'],
            'availability' => 'on_request',
            'booking_mode' => 'request',
            'rate_type' => 'manual_rate',
            'status' => 'published',
            'quoted_at' => now(),
            'expires_at' => now()->addDays(3),
        ], $overrides));
    }

    public function test_public_search_returns_customer_price_and_never_exposes_supplier_cost(): void
    {
        $rate = $this->rate();
        $response = $this->getJson('/api/v1/freight/rates?'.http_build_query([
            'origin_port_id' => $rate->origin_port_id,
            'destination_port_id' => $rate->destination_port_id,
            'container_type' => '40ft_hc',
            'currency' => 'USD',
            'quantity' => 2,
        ]));

        $response->assertOk()
            ->assertJsonPath('data.0.id', $rate->id)
            ->assertJsonPath('data.0.unit_price', '1150.00')
            ->assertJsonPath('data.0.total_price', '2350.00')
            ->assertJsonPath('data.0.booking_mode', 'request')
            ->assertJsonMissingPath('data.0.supplier_unit_cost');
    }

    public function test_expired_rates_and_rates_from_disabled_sources_are_not_searchable(): void
    {
        $expired = $this->rate(['expires_at' => now()->subMinute()]);
        $query = http_build_query([
            'origin_port_id' => $expired->origin_port_id,
            'destination_port_id' => $expired->destination_port_id,
            'container_type' => '40ft_hc',
            'currency' => 'USD',
        ]);
        $this->getJson('/api/v1/freight/rates?'.$query)->assertOk()->assertJsonCount(0, 'data');

        $disabledSource = FreightRateSource::create([
            'code' => 'test-disabled', 'name' => 'Disabled source', 'type' => 'provider',
            'tier' => 'free', 'is_active' => false,
        ]);
        $disabledSourceRate = $this->rate(['source_id' => $disabledSource->id, 'source_reference' => 'disabled']);
        $this->getJson('/api/v1/freight/rates?'.$query)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_search_requires_a_valid_route_and_container_type(): void
    {
        $this->getJson('/api/v1/freight/rates')->assertUnprocessable()
            ->assertJsonValidationErrors(['origin_port_id', 'destination_port_id', 'container_type', 'currency']);
    }
}
