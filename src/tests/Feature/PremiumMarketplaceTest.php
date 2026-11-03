<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PremiumMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $customer = Customer::create(['company_name' => 'UAE Company '.Str::random(6), 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);
        if ($role === 'owner') {
            return [$owner, $workspace];
        }
        $member = User::factory()->create(['customer_id' => $customer->id]);
        CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $member->id, 'role' => $role]);

        return [$member, $workspace];
    }

    private function listingData(array $extra = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'title' => 'Office chairs for Dubai businesses',
            'description' => 'Supply of office chairs with adjustable armrests for business offices in Dubai.',
            'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'currency' => 'AED',
            'commercial_details' => ['product_name' => 'Office chair', 'specifications' => 'Adjustable armrests', 'quantity' => '500.125', 'unit' => 'piece', 'unit_price' => '199.99', 'min_order_quantity' => '10', 'lead_time_days' => 14, 'delivery_terms' => 'dap', 'payment_terms' => 'To be agreed', 'target_market' => 'UAE', 'condition' => 'new', 'price_basis' => 'quotation']], $extra);
    }

    private function publish(User $user, array $extra = []): Opportunity
    {
        $this->actingAs($user)->post(route('opportunities.store'), $this->listingData($extra))->assertRedirect(route('company.marketplace'));

        return Opportunity::latest('id')->firstOrFail();
    }

    private function yachtData(): array
    {
        return ['title' => 'Dubai yacht with private cabins', 'category' => 'marine', 'estimated_value' => '950000.00', 'commercial_details' => ['subcategory' => 'yachts', 'features' => ['stabilizers', 'tender']], 'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'yacht', 'builder' => 'Test shipyard', 'length_m' => '25.50', 'beam_m' => '6.20', 'draft_m' => '1.80', 'cabins' => 4, 'guest_capacity' => 8, 'crew_capacity' => 3, 'engine_make' => 'Test engine', 'engine_hours' => 450, 'price_on_request' => false]];
    }

    public function test_yacht_creation_renders_independent_luxury_page_in_both_languages(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, $this->yachtData());
        $this->assertSame('6.20', $o->vessel->beam_m);
        $this->assertSame(450, $o->vessel->engine_hours);
        foreach (['ar', 'en'] as $lang) {
            $this->get(route('opportunities.show', [$o, 'lang' => $lang]))->assertOk()->assertViewIs('opportunities.marine.show')->assertSee('marine-listing')->assertSee('Test shipyard')->assertSee('25.50')->assertDontSee('marketplace.vessel.')->assertDontSee('notes');
        }
        $this->get(route('opportunities.index'))->assertOk()->assertSee('market-catalog')->assertSee('Dubai yacht with private cabins');
    }

    public function test_marine_update_is_scoped_and_touches_public_seo_date(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, $this->yachtData());
        $slug = $o->slug;
        $this->travel(1)->minutes();
        $data = $this->listingData($this->yachtData());
        $data['vessel']['cabins'] = 5;
        $this->patch(route('company.opportunities.update', $o), $data)->assertRedirect();
        $this->assertSame(5, $o->fresh()->vessel->cabins);
        $this->assertSame($slug, $o->fresh()->slug);
        $this->assertTrue($o->fresh()->seo_updated_at->gt($o->seo_updated_at));
        [$other] = $this->company();
        $this->actingAs($other)->patch(route('company.opportunities.update', $o), $data)->assertNotFound();
        $this->assertSame(5, $o->fresh()->vessel->cabins);
    }

    public function test_invalid_taxonomy_vessel_fields_and_attributes_do_not_publish(): void
    {
        [$u] = $this->company();
        foreach ([
            ['category' => 'food', 'commercial_details' => ['subcategory' => 'yachts']],
            ['commercial_details' => ['subcategory' => 'yachts'], 'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'boat']],
            ['vessel' => ['listing_type' => 'sale', 'vessel_category' => 'yacht', 'beam_m' => '-1']],
            ['commercial_details' => ['subcategory' => 'yachts', 'features' => ['invented']]],
            ['category' => 'electronics', 'commercial_details' => ['subcategory' => 'computers', 'attributes' => ['bedrooms' => 3]]],
        ] as $bad) {
            $data = $this->listingData(array_replace($this->yachtData(), $bad));
            $this->actingAs($u)->postJson(route('opportunities.store'), $data)->assertUnprocessable();
        }
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_nested_attribute_key_order_preserves_publication_idempotence(): void
    {
        [$u] = $this->company();
        $data = $this->listingData(['category' => 'automotive', 'commercial_details' => ['subcategory' => 'vehicles', 'attributes' => ['brand' => 'Test brand', 'mileage_km' => 100]]]);
        $this->actingAs($u)->post(route('opportunities.store'), $data)->assertRedirect();
        $data['commercial_details']['attributes'] = ['mileage_km' => 100, 'brand' => 'Test brand'];
        $this->post(route('opportunities.store'), $data)->assertRedirect();
        $this->assertDatabaseCount('opportunities', 1);
        $this->get(route('opportunities.show', Opportunity::first()))->assertOk()->assertViewIs('opportunities.show')->assertSee('Test brand');
    }

    public function test_price_facets_require_currency_and_do_not_compare_unit_prices(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, $this->yachtData());
        $this->getJson(route('opportunities.index', ['min_price' => '100']))->assertUnprocessable();
        $this->getJson(route('opportunities.index', ['sort' => 'price_asc']))->assertUnprocessable();
        $this->getJson(route('opportunities.index', ['category' => 'food', 'subcategory' => 'yachts']))->assertUnprocessable();
        $this->get(route('opportunities.index', ['currency' => 'USD', 'min_price' => '100']))->assertOk()->assertDontSee($o->title);
        $this->get(route('opportunities.index', ['category' => 'marine', 'subcategory' => 'yachts', 'currency' => 'AED', 'min_price' => '900000', 'sort' => 'price_asc']))->assertOk()->assertSee($o->title)->assertSee('noindex');
    }

    public function test_luxury_template_only_for_yacht_and_boat_sales_and_never_private_listings(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, $this->yachtData());
        $o->vessel->update(['listing_type' => 'wanted']);
        $this->get(route('opportunities.show', $o))->assertViewIs('opportunities.show');
        $o->vessel->update(['listing_type' => 'sale', 'vessel_category' => 'boat']);
        $this->get(route('opportunities.show', $o))->assertViewIs('opportunities.marine.show');
        $o->vessel->update(['vessel_category' => 'commercial_vessel']);
        $this->get(route('opportunities.show', $o))->assertViewIs('opportunities.show');
        $o->update(['marketplace_state' => 'closed']);
        $this->get(route('opportunities.show', $o))->assertNotFound();
    }

    public function test_forms_include_expanded_fields_without_extra_theme_control(): void
    {
        [$u] = $this->company();
        foreach (['en', 'ar'] as $lang) {
            $this->actingAs($u)->get(route('opportunities.create', ['lang' => $lang]))->assertOk()->assertSee('commercial_details[subcategory]', false)->assertSee('vessel[beam_m]',false)->assertSee('commercial_details[attributes][brand]',false)->assertDontSee('marketplace.subcategories.')->assertDontSee('data-theme-toggle');
        }
    }

    public function test_real_media_gallery_and_video_are_rendered_with_escaped_text(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, $this->yachtData());
        $o->media()->create(['type'=>'image','path'=>'opportunities/real-exterior.jpg','is_primary'=>true]);
        $o->media()->create(['type'=>'image','path'=>'opportunities/real-interior.jpg','is_primary'=>false]);
        $o->media()->create(['type'=>'video','path'=>'opportunities/real-tour.mp4','is_primary'=>false]);
        $o->update(['title'=>'Yacht <script>alert(1)</script>']);
        $this->get(route('opportunities.show', $o))->assertOk()->assertSee('real-exterior.jpg')->assertSee('real-interior.jpg')->assertSee('real-tour.mp4')->assertSee('marine-thumbnails')->assertSee('Yacht &lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('opportunities.index'))->assertOk()->assertSee('real-exterior.jpg');
    }
}
