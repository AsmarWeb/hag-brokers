<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\FreightRate;
use App\Models\FreightRateSource;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FreightRateComparisonPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_page_shows_customer_total_and_never_supplier_cost(): void
    {
        $origin = Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE']);
        $destination = Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA']);
        $carrier = Carrier::create(['name' => 'Test Carrier', 'code' => 'PAGE-TEST', 'is_active' => true]);
        $source = FreightRateSource::create(['code' => 'page-test', 'name' => 'HAG Test', 'type' => 'manual', 'tier' => 'internal', 'is_active' => true]);
        $rate = FreightRate::create([
            'source_id' => $source->id, 'carrier_id' => $carrier->id,
            'origin_port_id' => $origin->id, 'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc', 'min_quantity' => 1,
            'supplier_unit_cost' => 900, 'customer_unit_price' => 1000, 'currency' => 'USD',
            'surcharge_items' => [['name' => 'Docs', 'amount' => 25]],
            'included_charges' => ['Origin THC'], 'excluded_charges' => ['Customs'],
            'availability' => 'on_request', 'booking_mode' => 'request', 'rate_type' => 'manual_rate',
            'status' => 'published', 'quoted_at' => now(), 'expires_at' => now()->addDays(2),
        ]);

        $response = $this->get('/freight/rates?'.http_build_query([
            'origin_port_id' => $origin->id, 'destination_port_id' => $destination->id,
            'container_type' => '40ft_hc', 'currency' => 'USD', 'quantity' => 2,
        ]));

        $response->assertOk()->assertSee('Jebel Ali')->assertSee('Dammam')
            ->assertSee('2,025.00')->assertSee('USD')->assertSee('Origin THC')
            ->assertDontSee('900.00')->assertSee('not a final booking', false);
    }

    public function test_comparison_page_renders_search_form_without_query(): void
    {
        $this->get('/freight/rates')->assertOk()->assertSee('Compare ocean freight rates');
    }
}
