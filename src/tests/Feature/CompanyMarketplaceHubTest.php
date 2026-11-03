<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Platform\OpportunityModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompanyMarketplaceHubTest extends TestCase
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

    private function admin(): User
    {
        $a = User::factory()->create();
        $a->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $a;
    }

    private function leader($w, string $role = 'manager'): User
    {
        $u = User::factory()->create(['customer_id' => $w->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $u->id, 'role' => $role]);

        return $u;
    }

    private function moderate(User $admin, Opportunity $o, string $action = 'hide', int $version = 0): void
    {
        app(OpportunityModerationService::class)->apply($admin, $o, ['action' => $action, 'version' => $version, 'reason' => 'PRIVATE REVIEW NOTE must never reach the company.']);
    }

    public function test_company_sees_real_visibility_and_only_links_to_eligible_public_listing(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $admin = $this->admin();
        $this->actingAs($owner)->get(route('company.marketplace'))->assertOk()->assertViewHas('visibleIds', fn ($ids) => in_array($o->id, $ids))->assertSee(route('opportunities.show', $o), false);
        $this->moderate($admin, $o);
        foreach (['ar', 'en'] as $lang) {
            $this->get(route('company.marketplace', ['lang' => $lang]))->assertOk()->assertViewHas('visibleIds', fn ($ids) => ! in_array($o->id, $ids))->assertSee('hub-notice')->assertDontSee(route('opportunities.show', $o), false)->assertDontSee('PRIVATE REVIEW NOTE')->assertDontSee('company_hub.');
        }
    }

    public function test_only_current_company_leaders_receive_safe_database_notifications_once(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner);
        $manager = $this->leader($w);
        $member = $this->leader($w, 'member');
        [$other] = $this->company();
        $admin = $this->admin();
        $this->moderate($admin, $o);
        $this->assertSame(1, $owner->notifications()->count());
        $this->assertSame(1, $manager->notifications()->count());
        $this->assertSame(0, $member->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());
        $this->assertSame(0, $admin->notifications()->count());
        $payload = $owner->notifications()->first()->data;
        $this->assertSame('marketplace.moderation.hide', $payload['type']);
        $this->assertSame('/company/marketplace', $payload['url']);
        $this->assertArrayNotHasKey('reason', $payload);
        $this->assertStringNotContainsString('PRIVATE REVIEW NOTE', json_encode($payload));
        try {
            $this->moderate($admin, $o, 'hide', 0);
            $this->fail('Expected stale revision');
        } catch (ValidationException) {
        }
        $this->assertSame(1, $owner->notifications()->count());
        $this->moderate($admin, $o, 'note', 1);
        $this->assertSame(1, $owner->notifications()->count());
        $this->moderate($admin, $o, 'restore', 2);
        $this->assertSame(2, $owner->notifications()->count());
    }

    public function test_stale_membership_or_global_admin_role_never_receives_company_notice(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner);
        $stale = $this->leader($w);
        DB::table('users')->where('id', $stale->id)->update(['customer_id' => null]);
        $privileged = $this->leader($w);
        $privileged->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->moderate($this->admin(), $o);
        $this->assertSame(0, $stale->notifications()->count());
        $this->assertSame(0, $privileged->notifications()->count());
    }

    public function test_company_filters_and_metrics_are_scoped_and_do_not_release_quota(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner);
        $admin = $this->admin();
        $this->moderate($admin, $o);
        [$other] = $this->company();
        $otherListing = $this->publish($other, ['title' => 'Different company private listing title']);
        $this->actingAs($owner)->get(route('company.marketplace', ['listing_status' => 'hidden']))->assertOk()->assertSee($o->title)->assertDontSee($otherListing->title)->assertViewHas('stats', fn ($s) => $s['total'] === 1 && $s['hidden'] === 1 && $s['visible'] === 0)->assertViewHas('usage', fn ($usage) => $usage['active'] === 1);
        $this->get(route('company.marketplace', ['listing_status' => 'visible']))->assertOk()->assertDontSee($o->title);
        $this->getJson(route('company.marketplace', ['listing_status' => 'other_company']))->assertUnprocessable();
    }

    public function test_member_cannot_see_incoming_decisions_or_sensitive_pending_counter(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner);
        $member = $this->leader($w, 'member');
        $this->actingAs($member)->get(route('company.marketplace'))->assertOk()->assertViewHas('incoming', null)->assertViewHas('stats', fn ($s) => $s['pending'] === null)->assertDontSee('id="hub-incoming"', false)->assertDontSee(route('company.opportunities.edit', $o), false);
    }

    public function test_restore_notification_does_not_claim_expired_listing_is_public(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $admin = $this->admin();
        $this->moderate($admin, $o);
        $o->update(['expires_at' => now()->subMinute()]);
        $this->moderate($admin, $o, 'restore', 1);
        $this->actingAs($owner)->get(route('company.marketplace', ['listing_status' => 'expired']))->assertOk()->assertSee($o->title)->assertViewHas('visibleIds', fn ($ids) => $ids === []);
        $this->assertSame(1, $owner->notifications()->get()->filter(fn ($n) => $n->data['type'] === 'marketplace.moderation.restore')->count());
    }

    public function test_notification_failure_rolls_back_moderation_and_audit(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $admin = $this->admin();
        Event::listen(NotificationSending::class, function () {
            throw new \RuntimeException('Simulated notification failure');
        });
        try {
            $this->moderate($admin, $o);
            $this->fail('Expected notification failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated notification failure',$e->getMessage());
        }
        $this->assertDatabaseCount('opportunity_moderations',0);
        $this->assertDatabaseCount('opportunity_moderation_events',0);
        $this->assertSame(0,$owner->notifications()->count());
    }
}
