<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyWorkspaceUiTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Emirates Trading <script>bad</script>', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w, $c];
    }

    public function test_main_company_pages_use_user_layout_and_shared_navigation_in_both_languages(): void
    {
        [$u] = $this->owner();
        foreach (['ar', 'en'] as $language) {
            foreach (['company.marketplace', 'company.workspace', 'company.billing'] as $route) {
                $this->actingAs($u)->get(route($route, ['lang' => $language]))->assertOk()->assertSee('cw-navigation')->assertSee(route('dashboard'), false)->assertDontSee('class="market-header"', false)->assertDontSee('Test mode only — no real charges');
            }
        }
    }

    public function test_payment_preview_remains_explicit_and_live_confirmation_is_preserved(): void
    {
        [$u] = $this->owner();
        config(['platform_billing.mode' => 'test', 'platform_billing.enabled' => true, 'platform_billing.secret' => 'sk_test_fake']);
        $this->actingAs($u)->get(route('company.billing', ['lang' => 'en']))->assertOk()->assertSee('Payment preview only. No actual amount is charged.')->assertSee('Preview payment');
        config(['platform_billing.mode' => 'live', 'platform_billing.enabled' => true, 'platform_billing.checkout_enabled' => true, 'platform_billing.secret' => 'sk_live_fake', 'platform_billing.webhook_secret' => 'whsec_fake', 'platform_billing.prices' => ['business' => 'price_b', 'professional' => 'price_p'], 'app.url' => 'https://market.example.test', 'app.debug' => false]);
        $this->get(route('company.billing', ['lang' => 'en']))->assertOk()->assertSee('name="confirmed"', false)->assertDontSee('Payment preview only. No actual amount is charged.');
        config(['platform_billing.checkout_enabled' => false]);
        $this->get(route('company.billing', ['lang' => 'en']))->assertOk()->assertDontSee('name="confirmed"', false)->assertSee('New subscriptions are currently unavailable.');
    }

    public function test_member_navigation_does_not_expose_leader_actions(): void
    {
        [$owner,$w,$c] = $this->owner();
        $member = User::factory()->create(['customer_id' => $c->id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'member']);
        $this->actingAs($member)->get(route('company.marketplace'))->assertOk()->assertDontSee('href="'.route('company.billing').'"', false)->assertDontSee('href="'.route('company.profile.edit').'"', false)->assertDontSee('href="'.route('company.mail.edit').'"', false);
    }

    public function test_new_workspace_escapes_company_name_and_does_not_change_records(): void
    {
        [$u,$w,$c] = $this->owner();
        $before = $c->fresh()->getAttributes();
        $this->actingAs($u)->get(route('company.workspace'))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>bad</script>', false);
        $this->assertSame($before, $c->fresh()->getAttributes());
        $this->assertDatabaseCount('company_live_billing_accounts', 0);
        $this->assertDatabaseCount('company_billing_accounts',0);
    }
}
