<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityModerationEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOpportunityOversightTest extends TestCase
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

    private function admin(string $role = 'admin'): User
    {
        $u = User::factory()->create();
        $u->addRole(Role::firstOrCreate(['name' => $role], ['display_name' => $role]));

        return $u;
    }

    private function action(Opportunity $o, string $action = 'hide', int $version = 0, array $extra = [])
    {
        return $this->post(route('admin.opportunities.moderate', $o), array_replace(['action' => $action, 'version' => $version, 'reason' => 'Reviewed by the administrator for marketplace quality.', 'confirmed' => 1], $extra));
    }

    public function test_admin_and_super_admin_see_expanded_data_in_both_languages(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner, $this->yachtData());
        foreach (['admin', 'super-admin'] as $role) {
            $this->actingAs($this->admin($role));
            foreach (['en', 'ar'] as $lang) {
                $this->get(route('admin.opportunities.index', ['lang' => $lang]))->assertOk()->assertSee($o->title)->assertDontSee('admin_opportunities.title');
                $this->get(route('admin.opportunities.show', [$o, 'lang' => $lang]))->assertOk()->assertSee('Test shipyard')->assertSee('6.20')->assertSee('ao-moderation')->assertDontSee('marketplace.vessel.')->assertDontSee('admin_opportunities.');
            }
        }
    }

    public function test_hide_and_restore_use_shared_public_eligibility_and_preserve_source_records(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner, $this->yachtData());
        $before = $o->getAttributes();
        $v = $o->vessel->getAttributes();
        $usage = app(CompanyMarketplaceService::class)->usage($w);
        $this->actingAs($this->admin());
        $this->action($o)->assertRedirect(route('admin.opportunities.show', $o));
        $this->assertTrue($o->fresh()->moderation->hidden);
        $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($o));
        $this->get(route('opportunities.show', $o))->assertNotFound();
        $this->get(route('opportunities.index'))->assertDontSee($o->title);
        $this->get('/')->assertDontSee($o->title);
        $this->get('/sitemaps/opportunities-1.xml')->assertDontSee($o->slug);
        $this->action($o, 'restore', 1)->assertRedirect();
        $this->assertTrue(app(CompanyMarketplaceService::class)->isPublic($o));
        $this->assertSame($before, $o->fresh()->getAttributes());
        $this->assertSame($v, $o->fresh()->vessel->getAttributes());
        $this->assertSame($usage, app(CompanyMarketplaceService::class)->usage($w));
        $this->assertDatabaseCount('opportunity_moderation_events', 2);
    }

    public function test_restore_does_not_reopen_closed_expired_or_suspended_opportunity(): void
    {
        [$owner,$w] = $this->company();
        $o = $this->publish($owner, $this->yachtData());
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->action($o)->assertRedirect();
        $o->update(['marketplace_state' => 'closed']);
        $this->action($o, 'restore', 1)->assertRedirect();
        $this->get(route('opportunities.show', $o))->assertNotFound();
        $o->update(['marketplace_state' => 'published', 'expires_at' => now()->subMinute()]);
        $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($o));
        $o->update(['expires_at' => now()->addDay()]);
        $w->update(['status' => 'suspended']);
        $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($o));
    }

    public function test_stale_or_repeated_action_cannot_override_another_administrator(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->actingAs($this->admin());
        $this->action($o)->assertRedirect();
        $this->action($o, 'restore', 0)->assertSessionHasErrors('version');
        $this->action($o, 'hide', 1)->assertSessionHasErrors('action');
        $this->assertDatabaseCount('opportunity_moderation_events', 1);
        $this->assertTrue($o->fresh()->moderation->hidden);
    }

    public function test_internal_notes_are_private_and_cannot_change_listing_fields(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $before = $o->getAttributes();
        $this->actingAs($this->admin());
        $this->action($o, 'note', 0, ['reason' => 'PRIVATE ADMIN CASE <script>alert(1)</script>', 'title' => 'Override', 'verification_status' => 'verified', 'customer_id' => 999])->assertRedirect();
        $this->assertSame($before, $o->fresh()->getAttributes());
        $this->assertFalse($o->fresh()->moderation->hidden);
        $this->get(route('admin.opportunities.show', $o))->assertSee('PRIVATE ADMIN CASE')->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('opportunities.show', $o))->assertOk()->assertDontSee('PRIVATE ADMIN CASE');
    }

    public function test_guests_and_company_roles_cannot_moderate_or_read_admin_data(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->get(route('admin.opportunities.show', $o))->assertForbidden();
        $this->action($o)->assertForbidden();
        $this->app['auth']->forgetGuards();
        auth()->logout();
        $this->get(route('admin.opportunities.index'))->assertRedirect(route('login'));
        $regular = User::factory()->create();
        $this->actingAs($regular);
        $this->action($o)->assertForbidden();
        $this->assertDatabaseCount('opportunity_moderation_events', 0);
    }

    public function test_reason_confirmation_and_action_validation_are_required(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->actingAs($this->admin());
        foreach ([['reason' => ''], ['reason' => 'short'], ['confirmed' => 0], ['action' => 'delete'], ['version' => -1]] as $bad) {
            $this->action($o, 'hide', 0, $bad)->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('opportunity_moderations', 0);
        $this->assertDatabaseCount('opportunity_moderation_events', 0);
    }

    public function test_filters_use_real_publication_state_and_validate_dates_and_category(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner, $this->yachtData());
        $this->actingAs($this->admin());
        $this->action($o)->assertRedirect();
        $this->get(route('admin.opportunities.index', ['publication' => 'public']))->assertOk()->assertDontSee($o->title);
        $this->get(route('admin.opportunities.index', ['review' => 'hidden', 'category' => 'marine', 'subcategory' => 'yachts']))->assertOk()->assertSee($o->title);
        $this->getJson(route('admin.opportunities.index', ['category' => 'food', 'subcategory' => 'yachts']))->assertUnprocessable();
        $this->getJson(route('admin.opportunities.index', ['from' => '2026-11-04', 'to' => '2026-11-03']))->assertUnprocessable();
    }

    public function test_failed_audit_insert_rolls_back_state(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->actingAs($this->admin());
        OpportunityModerationEvent::creating(function () {
            throw new \RuntimeException('Simulated audit failure');
        });
        $this->action($o)->assertServerError();
        $this->assertDatabaseCount('opportunity_moderations', 0);
        $this->assertTrue(app(CompanyMarketplaceService::class)->isPublic($o));
    }

    public function test_moderation_history_cannot_be_edited_or_deleted(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->actingAs($this->admin());
        $this->action($o)->assertRedirect();
        $event = OpportunityModerationEvent::firstOrFail();
        try {
            $event->update(['reason' => 'Changed history']);
            $this->fail('Expected immutable history');
        } catch (\LogicException) {
        }
        try {
            $event->delete();
            $this->fail('Expected immutable history');
        } catch (\LogicException) {
        }
        $this->assertDatabaseCount('opportunity_moderation_events', 1);
    }

    public function test_company_edit_cannot_remove_administrative_hold(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $this->actingAs($this->admin());
        $this->action($o)->assertRedirect();
        $this->actingAs($owner)->patch(route('company.opportunities.update', $o), ['title' => $o->title, 'description' => $o->description, 'moderation' => ['hidden' => false]])->assertRedirect();
        $this->assertTrue($o->fresh()->moderation->hidden);
        $this->assertFalse(app(CompanyMarketplaceService::class)->isPublic($o));
    }

    public function test_large_history_and_interest_lists_are_paginated_without_public_exposure(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $admin = $this->admin();
        for ($n = 0; $n < 12; $n++) {
            app(\App\Services\Platform\OpportunityModerationService::class)->apply($admin, $o, ['action'=>'note','version'=>$n,'reason'=>'Internal review case number '.$n]);
            $o->interests()->create(['user_id'=>User::factory()->create()->id,'role'=>'buyer','message'=>'Private applicant message '.$n,'status'=>'pending']);
        }
        $response = $this->actingAs($admin)->get(route('admin.opportunities.show',$o));
        $response->assertOk()->assertViewHas('events',fn($events)=>$events->total()===12 && $events->count()===10)->assertViewHas('interests',fn($interests)=>$interests->total()===12 && $interests->count()===10);
        $this->get(route('admin.opportunities.show',[$o,'history_page'=>2,'interests_page'=>2]))->assertOk()->assertViewHas('events',fn($events)=>$events->count()===2)->assertViewHas('interests',fn($interests)=>$interests->count()===2);
        $this->get(route('opportunities.show',$o))->assertDontSee('Internal review case')->assertDontSee('Private applicant message');
    }

    public function test_created_date_filter_uses_the_whole_dubai_calendar_day(): void
    {
        [$owner] = $this->company();
        $o = $this->publish($owner);
        $o->forceFill(['created_at'=>'2026-11-02 20:00:00'])->save();
        $this->actingAs($this->admin());
        $this->get(route('admin.opportunities.index',['from'=>'2026-11-03','to'=>'2026-11-03']))->assertOk()->assertSee($o->title);
        $o->forceFill(['created_at'=>'2026-11-03 20:00:00'])->save();
        $this->get(route('admin.opportunities.index',['from'=>'2026-11-03','to'=>'2026-11-03']))->assertOk()->assertDontSee($o->title);
    }
}
