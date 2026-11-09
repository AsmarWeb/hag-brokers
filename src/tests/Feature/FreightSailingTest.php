<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminFreightSailingController;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\FreightSailing;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use App\Services\Freight\SailingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FreightSailingTest extends TestCase
{
    use RefreshDatabase;

    private function sailing(array $overrides = []): FreightSailing
    {
        $origin = Port::firstOrCreate(['unlocode' => 'AEJEA'], ['name' => 'Jebel Ali', 'country_code' => 'AE']);
        $destination = Port::firstOrCreate(['unlocode' => 'SADMM'], ['name' => 'Dammam', 'country_code' => 'SA']);
        $carrier = Carrier::firstOrCreate(['code' => 'TEST'], ['name' => 'Test carrier', 'is_active' => true]);

        return FreightSailing::create(array_replace([
            'origin_port_id' => $origin->id, 'destination_port_id' => $destination->id,
            'carrier_id' => $carrier->id, 'vessel_name' => 'Test vessel', 'voyage_number' => Str::random(6),
            'departure_at' => now()->addDays(3), 'arrival_at' => now()->addDays(5),
            'cargo_cutoff_at' => now()->addDays(2), 'container_types' => ['40ft_hc'], 'status' => 'published',
        ], $overrides));
    }

    private function requestData(FreightSailing $sailing): array
    {
        return [
            'origin_port_id' => $sailing->origin_port_id,
            'destination_port_id' => $sailing->destination_port_id,
            'freight_sailing_id' => $sailing->id,
            'sailing_revision' => app(SailingCatalog::class)->revision($sailing),
            'container_type' => '40ft_hc', 'cargo_type' => 'Equipment', 'quantity' => 2,
            'request_key' => (string) Str::uuid(),
        ];
    }

    public function test_public_search_shows_only_open_matching_sailings_and_renders_both_languages(): void
    {
        $open = $this->sailing();
        $this->sailing(['status' => 'draft']);
        $this->sailing(['status' => 'cancelled']);
        $this->sailing(['cargo_cutoff_at' => now()->subMinute()]);
        $this->sailing(['container_types' => ['20ft']]);
        $filters = http_build_query(['origin_port_id' => $open->origin_port_id,
            'destination_port_id' => $open->destination_port_id, 'container_type' => '40ft_hc']);
        $this->getJson('/api/v1/freight/sailings?'.$filters)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $open->id)
            ->assertJsonPath('data.0.availability', 'on_request');
        foreach (['ar', 'en'] as $locale) {
            $this->get('/freight/sailings?'.$filters.'&lang='.$locale)->assertOk()->assertSee($open->voyage_number);
        }
        $this->getJson('/api/v1/freight/sailings?origin_port_id='.$open->origin_port_id)
            ->assertUnprocessable()->assertJsonValidationErrors('destination_port_id');
    }

    public function test_dashboard_links_to_authenticated_sailing_search(): void
    {
        $sailing = $this->sailing();
        $url = route('dashboard.freight.sailings');
        $this->get($url)->assertRedirect(route('login'));

        $customer = Customer::create(['company_name' => 'Freight customer']);
        $user = User::factory()->create(['customer_id' => $customer->id]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee($url);

        $filters = http_build_query([
            'origin_port_id' => $sailing->origin_port_id,
            'destination_port_id' => $sailing->destination_port_id,
            'container_type' => '40ft_hc',
        ]);
        $this->get($url.'?'.$filters)->assertOk()
            ->assertViewIs('freight.dashboard-sailings')
            ->assertSee('action="'.$url.'"', false)
            ->assertSee($sailing->voyage_number)
            ->assertSee(route('freight.sailings.select', $sailing));
    }

    public function test_web_selection_prefills_form_and_persists_snapshot_once(): void
    {
        Notification::fake();
        $sailing = $this->sailing();
        $user = User::factory()->create(['customer_id' => Customer::create(['company_name' => 'Freight customer'])->id]);
        $this->actingAs($user)->get(route('freight.sailings.select', [
            'sailing' => $sailing, 'container_type' => '40ft_hc', 'quantity' => 2,
        ]))->assertRedirect(route('quotations.create', [
            'sailing' => $sailing->id, 'container_type' => '40ft_hc', 'quantity' => 2,
        ]));
        $this->get(route('quotations.create', ['sailing' => $sailing->id, 'container_type' => '40ft_hc', 'quantity' => 2]))
            ->assertOk()->assertSee('name="freight_sailing_id"', false);
        $data = $this->requestData($sailing);
        $this->post(route('quotations.store'), $data)->assertRedirect();
        $quote = Quotation::sole();
        $this->assertSame($sailing->voyage_number, $quote->sailing_snapshot['voyage_number']);
        $this->assertSame(2, (int) $quote->quantity);
        $this->get(route('quotations.show', $quote))->assertOk()->assertSee($sailing->voyage_number);
        $sailing->update(['status' => 'cancelled']);
        $this->post(route('quotations.store'), $data)->assertRedirect(route('quotations.show', $quote));
        $this->assertDatabaseCount('quotations', 1);
        $this->postJson(route('quotations.store'), array_replace($data, ['request_key' => (string) Str::uuid()]))
            ->assertUnprocessable()->assertJsonValidationErrors('freight_sailing_id');
    }

    public function test_modified_route_and_stale_sailing_are_rejected_before_writing(): void
    {
        $sailing = $this->sailing();
        $user = User::factory()->create(['customer_id' => Customer::create(['company_name' => 'Freight customer'])->id]);
        $data = $this->requestData($sailing);
        $this->actingAs($user)->postJson(route('quotations.store'), array_replace($data, ['container_type' => '20ft']))
            ->assertUnprocessable()->assertJsonValidationErrors('freight_sailing_id');
        $sailing->update(['arrival_at' => now()->addDays(6)]);
        $this->postJson(route('quotations.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('freight_sailing_id');
        $this->assertDatabaseCount('quotations', 0);
    }

    public function test_verified_admin_can_publish_and_cancel_a_sailing(): void
    {
        $draft = $this->sailing(['status' => 'draft']);
        $this->get('/admin/freight-sailings')->assertRedirect();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($admin)->get(route('admin.freight-sailings.edit', $draft))->assertOk();
        $data = $draft->only(['origin_port_id', 'destination_port_id', 'carrier_id', 'vessel_name', 'voyage_number', 'container_types']);
        foreach (['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field) {
            $data[$field] = $draft->$field->setTimezone('Asia/Dubai')->format('Y-m-d\TH:i');
        }
        $data['status'] = 'published';
        $data['revision'] = AdminFreightSailingController::revision($draft->fresh());
        $this->put(route('admin.freight-sailings.update', $draft), $data)->assertRedirect(route('admin.freight-sailings.index'));
        $this->assertSame('published', $draft->fresh()->status);
        $this->patch(route('admin.freight-sailings.cancel', $draft))->assertRedirect();
        $this->getJson('/api/v1/freight/sailings')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_freight_offer_is_published_accepted_and_confirmed_in_order(): void
    {
        Notification::fake();
        $sailing = $this->sailing();
        $customer = Customer::create(['company_name' => 'Freight customer']);
        $user = User::factory()->create(['customer_id' => $customer->id]);
        $outsider = User::factory()->create(['customer_id' => Customer::create(['company_name' => 'Another company'])->id]);
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($user)->post(route('quotations.store'), $this->requestData($sailing))->assertRedirect();
        $quote = Quotation::sole();
        $publish = route('admin.quotations.freight-booking.publish', $quote);
        $accept = route('quotations.freight-booking.accept', $quote);
        $confirm = route('admin.quotations.freight-booking.confirm', $quote);
        $offerData = [
            'expires_at' => now('Asia/Dubai')->addDay()->format('Y-m-d\TH:i'),
            'terms' => 'Port-to-port freight',
        ];
        $confirmation = ['carrier_reference' => 'REF-12345', 'space_confirmed' => '1'];

        $this->actingAs($user)->postJson($publish, $offerData)->assertForbidden();
        $this->actingAs($admin)->postJson($publish, $offerData)->assertUnprocessable();
        $this->post(route('admin.quotations.offers.store', $quote), [
            'carrier_id' => $sailing->carrier_id,
            'carrier_amount' => '712.34',
            'customer_amount' => '1000.25',
            'currency' => 'AED',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('admin.quotations.offers.approve', [$quote, $quote->offers()->sole()]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('admin.quotations.show', $quote))->assertOk()->assertSee($publish);
        $this->post($publish, $offerData)->assertRedirect()->assertSessionHasNoErrors();
        $offer = $quote->freightBookingOffer()->sole();
        $this->postJson($confirm, $confirmation)->assertUnprocessable();
        $this->post($publish, $offerData + ['revision' => $offer->revision])->assertRedirect()->assertSessionHasNoErrors();
        $revision = $offer->fresh()->revision;

        $this->actingAs($outsider)->postJson($accept, ['revision' => $revision])->assertNotFound();
        $this->actingAs($user)->get(route('quotations.show', $quote))
            ->assertOk()->assertSee('1000.25')->assertDontSee('712.34')->assertSee($accept);
        $this->postJson($accept, ['revision' => $offer->revision])->assertUnprocessable();
        $this->travel(25)->hours();
        $this->postJson($accept, ['revision' => $revision])->assertUnprocessable();
        $this->travelBack();
        $this->post($accept, ['revision' => $revision])->assertRedirect()->assertSessionHasNoErrors();
        $this->post($accept, ['revision' => $revision])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($admin)->postJson($confirm, ['carrier_reference' => 'REF-12345'])->assertUnprocessable();
        $originalArrival = $sailing->arrival_at;
        $sailing->update(['arrival_at' => $originalArrival->addDay()]);
        $this->postJson($confirm, $confirmation)->assertUnprocessable();
        $sailing->update(['arrival_at' => $originalArrival]);
        $this->post($confirm, $confirmation)->assertRedirect()->assertSessionHasNoErrors();
        $this->post($confirm, $confirmation)->assertRedirect()->assertSessionHasNoErrors();
        $this->postJson($confirm, array_replace($confirmation, ['carrier_reference' => 'REF-999']))->assertUnprocessable();
        $this->actingAs($user)->get(route('quotations.show', $quote))->assertOk()->assertSee('REF-12345');
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('quotation_history')->where('event', 'freight_offer_accepted')->count());
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('quotation_history')->where('event', 'freight_booking_confirmed')->count());
    }

    public function test_searates_probe_sends_expected_parameters_without_importing(): void
    {
        $this->sailing();
        config(['freight_sailings.searates_key' => 'fixture-key']);
        Http::preventStrayRequests();
        Http::fake(['https://schedules.searates.com/api/v2/schedules/by-points*' => Http::response(['data' => []])]);
        $this->artisan('freight:searates-probe')->expectsOutput('SeaRates HTTP status: 200')->assertSuccessful();
        Http::assertSent(fn ($request) => $request->hasHeader('X-API-KEY', 'fixture-key')
            && $request['origin'] === 'AEJEA' && $request['destination'] === 'SADMM'
            && $request['multimodal'] === 'false' && $request['cargo_type'] === 'GC');
        $this->assertDatabaseCount('freight_sailings', 1);
    }
}
