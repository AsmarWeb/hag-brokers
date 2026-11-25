<?php

namespace Tests\Feature;

use App\Models\MarineBoardInquiry;
use App\Models\MarineCargoListing;
use App\Models\MarineVesselListing;
use App\Models\Port;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarineBoardTest extends TestCase
{
    use RefreshDatabase;

    private function port(string $name): Port
    {
        return Port::create(['name' => $name, 'country_code' => 'AE', 'is_active' => true]);
    }

    private function cargo(array $overrides = []): MarineCargoListing
    {
        $origin = $this->port('Jebel Ali');
        $destination = $this->port('Umm Qasr');
        return MarineCargoListing::create(array_merge([
            'public_reference' => 'CG-'.Str::upper(Str::random(10)),
            'company_name' => 'Private Cargo Company LLC', 'contact_name' => 'Private Contact',
            'email' => 'private-cargo@example.test', 'phone' => '+971500000001',
            'cargo_type' => 'dry_bulk', 'description' => 'PRIVATE INTERNAL CARGO CONTACT NOTES - DO NOT PUBLISH.',
            'public_description' => 'Fertilizer cargo, 2500 MT, available from Jebel Ali to Umm Qasr.',
            'quantity' => 2500, 'loading_port_id' => $origin->id,
            'discharging_port_id' => $destination->id, 'laycan_from' => now()->addDays(10)->toDateString(),
            'laycan_to' => now()->addDays(15)->toDateString(), 'status' => 'published',
            'published_at' => now(), 'terms_accepted_at' => now(),
        ], $overrides));
    }

    private function vessel(array $overrides = []): MarineVesselListing
    {
        $port = $this->port('Fujairah');
        return MarineVesselListing::create(array_merge([
            'public_reference' => 'VS-'.Str::upper(Str::random(10)),
            'company_name' => 'Private Shipowner LLC', 'contact_name' => 'Private Owner',
            'email' => 'private-vessel@example.test', 'phone' => '+971500000002',
            'vessel_type' => 'bulk', 'dwt_capacity' => 32000, 'current_port_id' => $port->id,
            'open_date' => now()->addDays(4)->toDateString(), 'preferred_trading_area' => 'Arabian Gulf and Iraq',
            'status' => 'published', 'published_at' => now(), 'terms_accepted_at' => now(),
        ], $overrides));
    }

    public function test_public_board_has_two_tabs_and_exposes_no_company_or_contact_details(): void
    {
        $cargo = $this->cargo(['description' => 'PRIVATE INTERNAL CARGO CONTACT NOTES - DO NOT PUBLISH.']);
        $vessel = $this->vessel();
        $pending = $this->cargo(['public_reference' => 'CG-PENDING-0001', 'status' => 'pending_review', 'published_at' => null]);

        $this->get(route('maritime-board.index'))
            ->assertOk()->assertSee(__('maritime_board.cargoes_tab'))->assertSee(__('maritime_board.vessels_tab'))
            ->assertSee($cargo->public_reference)->assertSee($cargo->public_description)
            ->assertDontSee('PRIVATE INTERNAL CARGO CONTACT NOTES - DO NOT PUBLISH')
            ->assertDontSee($pending->public_reference)
            ->assertDontSee('Private Cargo Company LLC')->assertDontSee('private-cargo@example.test')
            ->assertDontSee('+971500000001');

        $this->get(route('maritime-board.index', ['tab' => 'vessels']))
            ->assertOk()->assertSee($vessel->public_reference)
            ->assertDontSee('Private Shipowner LLC')->assertDontSee('private-vessel@example.test')
            ->assertDontSee('+971500000002');
    }

    public function test_public_cargo_filter_limits_results_to_matching_port_and_type(): void
    {
        $cargo = $this->cargo(['cargo_type' => 'dry_bulk']);
        $other = $this->cargo(['cargo_type' => 'container']);
        $this->get(route('maritime-board.index', ['tab' => 'cargoes', 'cargo_type' => 'dry_bulk']))
            ->assertOk()->assertSee($cargo->public_reference)->assertDontSee($other->public_reference);
    }

    public function test_guest_can_submit_cargo_listing_but_it_remains_private_pending_review(): void
    {
        $origin = $this->port('Sohar');
        $destination = $this->port('Basra');
        $this->post(route('maritime-board.submit.store', 'cargo'), [
            'company_name' => 'Cargo Owner LLC', 'contact_name' => 'Cargo Contact',
            'email' => 'cargo@example.test', 'phone' => '+96890000000',
            'cargo_type' => 'dry_bulk', 'description' => 'A sufficiently detailed cargo description for review.',
            'quantity' => '1200.500',
            'loading_port_id' => $origin->id, 'discharging_port_id' => $destination->id,
            'laycan_from' => now()->addDays(7)->toDateString(), 'laycan_to' => now()->addDays(12)->toDateString(),
            'terms_accepted' => '1',
        ])->assertRedirect(route('maritime-board.index'));

        $listing = MarineCargoListing::sole();
        $this->assertSame('pending_review', $listing->status);
        $this->assertNull($listing->published_at);
        $this->assertNotNull($listing->terms_accepted_at);
        $this->get(route('maritime-board.index'))->assertDontSee($listing->public_reference);
    }

    public function test_vessel_listing_requires_terms_and_a_position(): void
    {
        $this->post(route('maritime-board.submit.store', 'vessel'), [
            'company_name' => 'Operator LLC', 'contact_name' => 'Operator Contact',
            'email' => 'operator@example.test', 'phone' => '+971500000003',
            'vessel_type' => 'bulk', 'dwt_capacity' => 15000,
            'open_date' => now()->addDays(5)->toDateString(), 'preferred_trading_area' => 'Gulf / Iraq',
        ])->assertSessionHasErrors(['terms_accepted', 'current_position']);

        $this->assertDatabaseCount('marine_vessel_listings', 0);
    }

    public function test_guest_can_submit_open_vessel_listing_pending_hag_review(): void
    {
        $port = $this->port('Khorfakkan');
        $this->post(route('maritime-board.submit.store', 'vessel'), [
            'company_name' => 'Open Vessel Operator LLC', 'contact_name' => 'Fleet Manager',
            'email' => 'fleet@example.test', 'phone' => '+971500000005',
            'vessel_type' => 'bulk', 'dwt_capacity' => '28500', 'current_port_id' => $port->id,
            'open_date' => now()->addDays(6)->toDateString(), 'preferred_trading_area' => 'Gulf, Iraq and Oman',
            'terms_accepted' => '1',
        ])->assertRedirect(route('maritime-board.index'));

        $listing = MarineVesselListing::sole();
        $this->assertSame('pending_review', $listing->status);
        $this->assertNull($listing->published_at);
        $this->assertSame($port->id, $listing->current_port_id);
        $this->get(route('maritime-board.index', ['tab' => 'vessels']))
            ->assertDontSee($listing->public_reference)
            ->assertDontSee('Open Vessel Operator LLC')
            ->assertDontSee('fleet@example.test');
    }

    public function test_public_inquiry_is_recorded_for_published_listing_only(): void
    {
        $cargo = $this->cargo();
        $this->post(route('maritime-board.inquiry.store', ['kind' => 'cargo', 'reference' => $cargo->public_reference]), [
            'name' => 'Interested Charterer', 'company_name' => 'Charterer LLC',
            'email' => 'charterer@example.test', 'phone' => '+971500000004',
            'message' => 'Please share the commercial terms and cargo particulars through HAG.',
            'terms_accepted' => '1',
        ])->assertRedirect(route('maritime-board.index', ['tab' => 'cargoes']));

        $inquiry = MarineBoardInquiry::sole();
        $this->assertSame($cargo->id, $inquiry->cargo_listing_id);
        $this->assertNull($inquiry->vessel_listing_id);
        $this->assertSame('new', $inquiry->status);

        $cargo->update(['status' => 'closed']);
        $this->post(route('maritime-board.inquiry.store', ['kind' => 'cargo', 'reference' => $cargo->public_reference]), [
            'name' => 'Second User', 'company_name' => 'Second LLC', 'email' => 'second@example.test',
            'phone' => '123456789', 'message' => 'I would like to receive more information about the cargo.', 'terms_accepted' => '1',
        ])->assertNotFound();
        $this->assertDatabaseCount('marine_board_inquiries', 1);
    }

    public function test_only_admin_can_review_and_publish_a_listing(): void
    {
        $listing = $this->cargo(['status' => 'pending_review', 'published_at' => null]);
        $ordinary = User::factory()->create();
        $this->actingAs($ordinary)->patch(route('admin.maritime-board.review', ['kind' => 'cargo', 'listing' => $listing->id]), [
            'status' => 'published',
        ])->assertForbidden();

        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($admin)->patch(route('admin.maritime-board.review', ['kind' => 'cargo', 'listing' => $listing->id]), [
            'status' => 'published', 'review_notes' => 'Verified with cargo owner.',
        ])->assertSessionHasErrors('public_description');
        $this->assertSame('pending_review', $listing->fresh()->status);

        $this->patch(route('admin.maritime-board.review', ['kind' => 'cargo', 'listing' => $listing->id]), [
            'status' => 'published',
            'public_description' => 'Verified fertilizer cargo, 2500 MT, Jebel Ali to Umm Qasr.',
            'review_notes' => 'Verified with cargo owner.',
        ])->assertRedirect();

        $listing->refresh();
        $this->assertSame('published', $listing->status);
        $this->assertNotNull($listing->published_at);
        $this->assertSame($admin->id, $listing->reviewed_by);
        $this->get(route('maritime-board.index'))
            ->assertSee($listing->public_reference)
            ->assertSee($listing->public_description)
            ->assertDontSee($listing->description);
    }

    public function test_expired_cargo_and_vessel_positions_are_hidden_and_cannot_receive_inquiries(): void
    {
        $cargo = $this->cargo([
            'public_reference' => 'CG-EXPIRED-0001',
            'laycan_from' => now('Asia/Dubai')->subDays(5)->toDateString(),
            'laycan_to' => now('Asia/Dubai')->subDay()->toDateString(),
        ]);
        $vessel = $this->vessel([
            'public_reference' => 'VS-EXPIRED-0001',
            'open_date' => now('Asia/Dubai')->subDay()->toDateString(),
        ]);

        $this->get(route('maritime-board.index', ['tab' => 'cargoes']))
            ->assertOk()->assertDontSee($cargo->public_reference);
        $this->get(route('maritime-board.index', ['tab' => 'vessels']))
            ->assertOk()->assertDontSee($vessel->public_reference);

        $this->get(route('maritime-board.inquiry.create', [
            'kind' => 'cargo', 'reference' => $cargo->public_reference,
        ]))->assertNotFound();
        $this->get(route('maritime-board.inquiry.create', [
            'kind' => 'vessel', 'reference' => $vessel->public_reference,
        ]))->assertNotFound();
    }

    public function test_admin_cannot_publish_cargo_with_contact_details_in_public_description(): void
    {
        $listing = $this->cargo(['status' => 'pending_review', 'published_at' => null]);
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        $this->actingAs($admin)->patch(route('admin.maritime-board.review', ['kind' => 'cargo', 'listing' => $listing->id]), [
            'status' => 'published',
            'public_description' => 'Fertilizer cargo available. Call +971500000099 or broker@example.test.',
        ])->assertSessionHasErrors('public_description');

        $this->assertSame('pending_review', $listing->fresh()->status);
        $this->assertNull($listing->fresh()->published_at);
    }

    public function test_vessel_public_text_rejects_contact_details(): void
    {
        $port = $this->port('Fujairah');
        $this->post(route('maritime-board.submit.store', 'vessel'), [
            'company_name' => 'Operator LLC', 'contact_name' => 'Operator Contact',
            'email' => 'operator@example.test', 'phone' => '+971500000003',
            'vessel_type' => 'bulk', 'dwt_capacity' => 15000, 'current_port_id' => $port->id,
            'current_position' => 'Call +971500000099',
            'open_date' => now()->addDays(5)->toDateString(),
            'preferred_trading_area' => 'Gulf broker@example.test',
            'terms_accepted' => '1',
        ])->assertSessionHasErrors(['current_position', 'preferred_trading_area']);

        $this->assertDatabaseCount('marine_vessel_listings', 0);
    }

    public function test_submission_rejects_expired_cargo_laycan_and_vessel_open_date(): void
    {
        $origin = $this->port('Sohar');
        $destination = $this->port('Basra');
        $this->post(route('maritime-board.submit.store', 'cargo'), [
            'company_name' => 'Cargo Owner LLC', 'contact_name' => 'Cargo Contact',
            'email' => 'cargo-expired@example.test', 'phone' => '+96890000000',
            'cargo_type' => 'dry_bulk', 'description' => 'A sufficiently detailed cargo description for review.',
            'quantity' => '1200.500', 'loading_port_id' => $origin->id,
            'discharging_port_id' => $destination->id,
            'laycan_from' => now('Asia/Dubai')->subDays(5)->toDateString(),
            'laycan_to' => now('Asia/Dubai')->subDay()->toDateString(),
            'terms_accepted' => '1',
        ])->assertSessionHasErrors('laycan_to');

        $this->post(route('maritime-board.submit.store', 'vessel'), [
            'company_name' => 'Operator LLC', 'contact_name' => 'Operator Contact',
            'email' => 'operator-expired@example.test', 'phone' => '+971500000003',
            'vessel_type' => 'bulk', 'dwt_capacity' => '15000',
            'current_position' => 'Fujairah anchorage',
            'open_date' => now('Asia/Dubai')->subDay()->toDateString(),
            'preferred_trading_area' => 'Gulf / Iraq', 'terms_accepted' => '1',
        ])->assertSessionHasErrors('open_date');

        $this->assertDatabaseCount('marine_cargo_listings', 0);
        $this->assertDatabaseCount('marine_vessel_listings', 0);
    }

}
