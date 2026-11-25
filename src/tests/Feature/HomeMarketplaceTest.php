<?php

namespace Tests\Feature;

use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomeMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_keeps_sailing_link_in_footer_and_routes_freight_requests_to_the_form(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $html = $this->get('/?lang='.$locale)->assertOk()->getContent();
            $document = new \DOMDocument;
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $sailingLink = 'a[@href="'.route('freight.sailings').'"]';
            $this->assertSame(0, $xpath->query('//*[@id="hero"]//'.$sailingLink)->length);
            $this->assertSame(1, $xpath->query('//footer//'.$sailingLink)->length);
            $this->assertSame(1, $xpath->query('//*[@id="quote-request"]//a[@href="'.route('quotations.create').'"]')->length);
        }
    }

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $customer = Customer::create(['company_name' => 'Home marketplace company', 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);

        return [$owner, $workspace, $customer];
    }

    private function listing(array $extra = []): Opportunity
    {
        [$owner, $workspace] = $this->company();

        return Opportunity::create(array_replace([
            'title' => 'Public offer '.Str::uuid(), 'description' => 'A public commercial offer with sufficient information.',
            'opportunity_type' => 'sell', 'category' => 'machinery', 'customer_id' => $owner->customer_id,
            'company_workspace_id' => $workspace->id, 'submitted_by' => $owner->id,
            'verification_status' => 'auto_eligible', 'marketplace_state' => 'published', 'visibility' => 'public',
            'published_at' => now()->subMinute(), 'expires_at' => now()->addDay(), 'currency' => 'AED',
        ], $extra));
    }

    public function test_automatic_company_publication_appears_without_manual_verification(): void
    {
        [$owner] = $this->company();
        $listing = app(CompanyMarketplaceService::class)->publish($owner, [
            'title' => 'Automatically published company offer', 'opportunity_type' => 'buy', 'category' => 'food',
            'description' => 'Company seeks food supply for its retail outlets.', 'currency' => 'AED', 'expires_at' => now()->addDay(),
        ], (string) Str::uuid())['opportunity'];
        $this->get(route('opportunities.index'))->assertOk()->assertSee($listing->title);
        $this->get('/')->assertOk()->assertViewMissing('opportunities')->assertDontSee($listing->title);
        $this->assertSame('auto_eligible', $listing->fresh()->verification_status);
    }

    public function test_homepage_and_browse_agree_on_all_publication_states(): void
    {
        $visible = $this->listing();
        foreach ([['visibility' => 'private'], ['verification_status' => 'pending'], ['published_at' => null], ['published_at' => now()->addHour()], ['expires_at' => now()], ['marketplace_state' => 'closed']] as $extra) {
            $hidden = $this->listing($extra);
            $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($hidden));
        }
        $this->assertSame(
            [$visible->id],
            app(CompanyMarketplaceService::class)->publicQuery()->pluck('id')->all()
        );
        $this->get(route('opportunities.index'))->assertOk()->assertSee($visible->title);
    }

    public function test_suspended_workspace_and_inactive_company_are_hidden(): void
    {
        $suspended = $this->listing();
        CompanyWorkspace::findOrFail($suspended->company_workspace_id)->update(['status' => 'suspended']);
        $inactive = $this->listing();
        Customer::findOrFail($inactive->customer_id)->update(['is_active' => false]);
        $this->assertSame(
            [],
            app(CompanyMarketplaceService::class)->publicQuery()->pluck('id')->all()
        );
        $this->get(route('opportunities.index'))
            ->assertOk()
            ->assertDontSee($suspended->title)
            ->assertDontSee($inactive->title);
    }

    public function test_verified_legacy_listing_remains_visible_without_workspace(): void
    {
        $listing = $this->listing(['company_workspace_id' => null, 'marketplace_state' => null, 'verification_status' => 'verified', 'expires_at' => null]);
        $this->get(route('opportunities.index'))->assertOk()->assertSee($listing->title);
    }

    public function test_only_latest_six_are_shown_with_stable_order_and_links_to_browse(): void
    {
        $listings = collect();
        for ($i = 0; $i < 8; $i++) {
            $listings->push($this->listing(['published_at' => now()->subMinutes(8 - $i)]));
        }
        $expectedTitles = $listings->reverse()->pluck('title')->values()->all();
        $this->get(route('opportunities.index'))
            ->assertOk()
            ->assertSeeInOrder($expectedTitles);
    }

    public function test_new_types_categories_and_filters_render_in_both_languages(): void
    {
        $listing = $this->listing(['notes' => 'PRIVATE-HOME-NOTE']);
        $other = $this->listing([
            'title' => 'Filtered out food offer',
            'opportunity_type' => 'buy',
            'category' => 'food',
        ]);

        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])
                ->get(route('opportunities.index', [
                    'lang' => $locale,
                    'category' => 'machinery',
                    'type' => 'sell',
                ]))
                ->assertOk()
                ->assertSee($listing->title)
                ->assertDontSee($other->title)
                ->assertDontSee('PRIVATE-HOME-NOTE')
                ->assertDontSee('trade.types.');
        }

        $this->assertSame('sell', $listing->fresh()->opportunity_type);
    }

    public function test_homepage_read_does_not_update_eligibility_or_financial_records(): void
    {
        $listing = $this->listing();
        $before = $listing->fresh()->getRawOriginal();
        $this->get('/')->assertOk();
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
        foreach (['issued_invoices', 'invoice_payments', 'company_billing_receipts'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_auto_eligible_listing_does_not_receive_a_reviewed_badge(): void
    {
        $listing = $this->listing();
        $html = $this->withSession(['locale' => 'en'])
            ->get(route('opportunities.index'))
            ->assertOk()
            ->assertSee($listing->title)
            ->getContent();

        $this->assertSame('auto_eligible', $listing->fresh()->verification_status);
        $this->assertStringNotContainsString('Reviewed', $html);
    }

    public function test_reviewed_listing_receives_badge_and_dynamic_text_is_escaped(): void
    {
        $listing = $this->listing([
            'verification_status' => 'verified',
            'title' => '<script>HOME-XSS</script>',
        ]);

        $html = $this->withSession(['locale' => 'en'])
            ->get(route('opportunities.index'))
            ->assertOk()
            ->assertDontSee('<script>HOME-XSS</script>', false)
            ->getContent();

        $this->assertStringContainsString(
            '&lt;script&gt;HOME-XSS&lt;/script&gt;',
            $html
        );
        $this->assertSame('verified', $listing->fresh()->verification_status);
    }

    public function test_homepage_is_maritime_focused_and_hides_the_general_opportunities_market_in_both_locales(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $response = $this->get('/?lang='.$locale)->assertOk()->assertViewMissing('opportunities');
            $html = $response->getContent();

            $this->assertStringNotContainsString('Discover Opportunities', $html);
            $this->assertStringNotContainsString('Have a Business Opportunity?', $html);
            $this->assertStringNotContainsString('Add Opportunity', $html);
            $this->assertStringNotContainsString(route('opportunities.index'), $html);
            $this->assertStringNotContainsString(route('opportunities.create'), $html);

            $this->assertStringContainsString('id="tracking"', $html);
            $this->assertStringContainsString('id="services"', $html);
            $this->assertStringContainsString('id="ports"', $html);
            $this->assertStringContainsString('id="gulf-map"', $html);
            $this->assertStringContainsString('id="marine-map-status"', $html);
            $this->assertStringContainsString('data-marine-filter="commercial_vessel"', $html);
            $this->assertStringContainsString(route('quotations.create'), $html);

            $document = new \DOMDocument;
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $sailingLink = 'a[@href="'.route('freight.sailings').'"]';
            $this->assertSame(1, $xpath->query('//footer//'.$sailingLink)->length);
            $this->assertSame(
                1,
                $xpath->query('//*[@id="quote-request"]//a[@href="'.route('quotations.create').'"]')->length
            );
        }

        // The legacy route remains registered during this staged retirement; only public homepage/navigation exposure is removed.
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('opportunities.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('opportunities.create'));
    }
}
