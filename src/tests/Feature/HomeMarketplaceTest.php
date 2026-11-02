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
        $this->get('/')->assertOk()->assertViewHas('opportunities', fn ($items) => $items->contains('id', $listing->id))->assertSee($listing->title);
        $this->assertSame('auto_eligible', $listing->fresh()->verification_status);
    }

    public function test_homepage_and_browse_agree_on_all_publication_states(): void
    {
        $visible = $this->listing();
        foreach ([['visibility' => 'private'], ['verification_status' => 'pending'], ['published_at' => null], ['published_at' => now()->addHour()], ['expires_at' => now()], ['marketplace_state' => 'closed']] as $extra) {
            $hidden = $this->listing($extra);
            $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($hidden));
        }
        $this->get('/')->assertOk()->assertViewHas('opportunities', fn ($items) => $items->pluck('id')->all() === [$visible->id]);
    }

    public function test_suspended_workspace_and_inactive_company_are_hidden(): void
    {
        $suspended = $this->listing();
        CompanyWorkspace::findOrFail($suspended->company_workspace_id)->update(['status' => 'suspended']);
        $inactive = $this->listing();
        Customer::findOrFail($inactive->customer_id)->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertViewHas('opportunities', fn ($items) => $items->isEmpty());
    }

    public function test_verified_legacy_listing_remains_visible_without_workspace(): void
    {
        $listing = $this->listing(['company_workspace_id' => null, 'marketplace_state' => null, 'verification_status' => 'verified', 'expires_at' => null]);
        $this->get('/')->assertOk()->assertViewHas('opportunities', fn ($items) => $items->contains('id', $listing->id));
    }

    public function test_only_latest_six_are_shown_with_stable_order_and_links_to_browse(): void
    {
        $listings = collect();
        for ($i = 0; $i < 8; $i++) {
            $listings->push($this->listing(['published_at' => now()->subMinutes(8 - $i)]));
        }
        $expected = $listings->reverse()->take(6)->pluck('id')->values()->all();
        $this->get('/')->assertOk()->assertViewHas('opportunities', fn ($items) => $items->pluck('id')->all() === $expected)->assertSee(route('opportunities.index'));
    }

    public function test_new_types_categories_and_filters_render_in_both_languages(): void
    {
        $listing = $this->listing(['notes' => 'PRIVATE-HOME-NOTE']);
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/')->assertOk()->assertSee('data-type="sell"', false)->assertSee('data-filter="sell"', false)->assertSee('data-filter="machinery"', false)->assertDontSee('PRIVATE-HOME-NOTE')->assertDontSee('trade.types.');
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
        $this->listing();
        $html = $this->withSession(['locale' => 'en'])->get('/')->assertOk()->getContent();
        preg_match_all('/<article\\s+class="opportunity-card.*?<\\/article>/s', $html, $cards);
        $this->assertCount(1, $cards[0]);
        $this->assertStringNotContainsString('Reviewed', $cards[0][0]);
    }

    public function test_reviewed_listing_receives_badge_and_dynamic_text_is_escaped(): void
    {
        $this->listing(['verification_status' => 'verified', 'title' => '<script>HOME-XSS</script>']);
        $html = $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertDontSee('<script>HOME-XSS</script>', false)->getContent();
        preg_match_all('/<article\\s+class="opportunity-card.*?<\\/article>/s', $html, $cards);
        $this->assertCount(1, $cards[0]);
        $this->assertStringContainsString('Reviewed', $cards[0][0]);
    }
}
