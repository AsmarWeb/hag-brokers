<?php

namespace Tests\Feature;

use App\Models\CompanyAccessGrant;
use App\Models\CompanyBillingAccount;
use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\PlatformPlan;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class FreeCompanyLaunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform_billing.free_launch' => true, 'platform_billing.enabled' => true, 'platform_billing.secret' => 'sk_test_fixture', 'platform_billing.mode' => 'test']);
        Http::preventStrayRequests();
    }

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Free launch company', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w, $c];
    }

    private function snapshot(string $code = 'business'): array
    {
        return PlatformPlan::where('code', $code)->firstOrFail()->only(['code', 'name_ar', 'name_en', 'monthly_amount_minor', 'currency', 'seats', 'active_opportunities', 'monthly_applications']);
    }

    private function billingAccount($w): CompanyBillingAccount
    {
        return CompanyBillingAccount::create(['company_workspace_id' => $w->id, 'customer_key' => (string) Str::uuid(), 'stripe_customer_id' => 'cus_fake_test', 'stripe_subscription_id' => 'sub_fake_test', 'status' => 'active', 'plan_code' => 'business', 'plan_snapshot' => $this->snapshot(), 'paid_until' => now()->addMonth()]);
    }

    public function test_both_languages_show_free_and_coming_soon_without_prices_or_checkout(): void
    {
        [$u] = $this->company();
        foreach (['ar', 'en'] as $locale) {
            $r = $this->actingAs($u)->get(route('company.billing', ['lang' => $locale]));
            $r->assertOk()->assertSee(__('free_launch.free'))->assertSee(__('free_launch.soon'))->assertSee(__('free_launch.pricing_later'))->assertDontSee('199')->assertDontSee('499')->assertDontSee('name="plan"', false)->assertDontSee('Preview payment')->assertDontSee('Test mode only');
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('company_billing_accounts', 0);
    }

    public function test_direct_checkout_is_blocked_before_any_provider_request(): void
    {
        [$u] = $this->company();
        $this->actingAs($u)->postJson(route('company.billing.checkout'), ['plan' => 'business', 'confirmed' => 1])->assertUnprocessable()->assertJsonValidationErrors('billing');
        config(['platform_billing.mode' => 'live', 'platform_billing.checkout_enabled' => true]);
        $this->postJson(route('company.billing.checkout'), ['plan' => 'professional', 'confirmed' => 1])->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertDatabaseCount('company_billing_accounts', 0);
        $this->assertDatabaseCount('company_live_billing_accounts', 0);
    }

    public function test_test_subscription_never_grants_paid_limits_and_history_is_preserved(): void
    {
        [$u,$w] = $this->company();
        $a = $this->billingAccount($w);
        $before = $a->fresh()->getAttributes();
        $plan = app(CompanyWorkspaceService::class)->plan($w);
        $this->assertSame('free', $plan['code']);
        $this->assertSame('free', $plan['source']);
        $this->assertSame(1, $plan['seats']);
        $this->actingAs($u)->get(route('company.workspace'))->assertOk();
        $this->get(route('company.billing'))->assertOk()->assertDontSee('sub_fake_test');
        $this->assertEquals($before, $a->fresh()->getAttributes());
        Http::assertNothingSent();
    }

    public function test_existing_trial_is_preserved_but_does_not_override_free_launch(): void
    {
        [$u,$w] = $this->company();
        CompanyAccessGrant::create(['company_workspace_id' => $w->id, 'source' => 'manual_trial', 'plan_snapshot' => $this->snapshot('professional'), 'expires_at' => now()->addMonth(), 'reason' => 'Earlier evaluation', 'created_by' => $u->id, 'created_at' => now()]);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->assertDatabaseCount('company_access_grants', 2);
    }

    public function test_existing_members_and_listings_are_not_deleted_when_limits_reduce(): void
    {
        [$u,$w,$c] = $this->company();
        $this->billingAccount($w);
        $member = User::factory()->create(['customer_id' => $c->id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'manager']);
        for ($i = 0; $i < 2; $i++) {
            Opportunity::create(['company_workspace_id' => $w->id, 'customer_id' => $c->id, 'title' => 'Existing listing '.$i, 'description' => 'A previously published commercial opportunity.', 'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'stage' => 'new', 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'marketplace_state' => 'published', 'published_at' => now()->subMinute(), 'expires_at' => now()->addMonth()]);
        }
        $this->actingAs($u)->get(route('company.marketplace'))->assertOk()->assertSee('Existing listing 0')->assertSee('Existing listing 1');
        $this->postJson(route('opportunities.store'), ['request_key' => (string) Str::uuid(), 'title' => 'Another office supply', 'description' => 'A commercial listing for UAE office buyers.', 'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'country' => 'UAE', 'city' => 'Dubai', 'currency' => 'AED'])->assertUnprocessable()->assertJsonValidationErrors('marketplace');
        $this->assertDatabaseCount('opportunities', 2);
        $this->assertDatabaseCount('company_members', 2);
    }

    public function test_closing_launch_mode_restores_existing_configuration_without_data_conversion(): void
    {
        [$u,$w] = $this->company();
        $a = $this->billingAccount($w);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        config(['platform_billing.free_launch' => false]);
        $this->assertSame('business', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->assertDatabaseCount('company_billing_accounts', 1);
        Http::assertNothingSent();
    }

    public function test_new_manual_trial_is_blocked_during_launch(): void
    {
        [$u,$w] = $this->company();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($admin)->postJson(route('admin.platform.trial', $w), ['plan_id' => PlatformPlan::where('code', 'professional')->first()->id, 'days' => 7, 'reason' => 'Pilot upgrade'])->assertUnprocessable();
        $this->assertDatabaseCount('company_access_grants', 1);
    }

    public function test_existing_real_paid_access_and_cancellation_are_not_misrepresented_as_free(): void
    {
        [$u,$w] = $this->company();
        config(['platform_billing.mode' => 'live', 'platform_billing.secret' => 'sk_live_fake', 'platform_billing.webhook_secret' => 'whsec_fake', 'platform_billing.prices' => ['business' => 'price_b', 'professional' => 'price_p'], 'app.url' => 'https://market.example.test', 'app.debug' => false]);
        $a = $this->billingAccount($w);
        $this->assertSame('stripe_live', app(CompanyWorkspaceService::class)->plan($w)['source']);
        $this->actingAs($u)->get(route('company.billing'))->assertOk()->assertSee(__('free_launch.legacy'))->assertDontSee('name="plan"', false);
        $this->postJson(route('company.billing.cancel'), ['cancel' => 0])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_launch_check_is_readonly_and_reports_checkout_closed(): void
    {
        $this->company();
        config(['platform_billing.checkout_enabled' => true]);
        $this->artisan('platform:billing-check')->expectsOutput('Free launch: ON')->expectsOutput('New live checkout: DISABLED')->assertSuccessful();
        Http::assertNothingSent();
    }
}
