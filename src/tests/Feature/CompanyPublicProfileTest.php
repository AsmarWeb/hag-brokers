<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyPublicProfile;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CompanyPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    private function company(): array
    {
        $c = Customer::create(['company_name' => 'Private Legal Company', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w, $c];
    }

    private function data(array $extra = []): array
    {
        return array_replace(['version' => 0, 'name_en' => 'Dubai Office Supply', 'about_en' => str_repeat('We supply office furniture for UAE businesses. ', 4), 'sector' => 'consumer_goods', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'specialties' => 'Office desks and chairs', 'markets' => 'UAE', 'website' => 'https://example.com', 'is_published' => 1], $extra);
    }

    private function save(User $u, array $extra = [])
    {
        return $this->actingAs($u)->put(route('company.profile.update'), $this->data($extra));
    }

    public function test_editor_uses_user_layout_and_public_profile_uses_market_layout(): void
    {
        [$u] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $editor = $this->get(route('company.profile.edit'))->assertOk()->assertSee('cp-studio')->assertSee(route('dashboard'), false)->assertDontSee('class="market-header"', false);
        $public = $this->get(route('companies.show', $p->slug))->assertOk()->assertSee('cp-public-hero')->assertSee('class="market-header"', false)->assertDontSee('name="version"', false);

    }

    public function test_draft_is_private_and_preview_does_not_publish(): void
    {
        [$u] = $this->company();
        $this->save($u, ['is_published' => 0])->assertRedirect(route('company.profile.edit'));
        $p = CompanyPublicProfile::firstOrFail();
        $this->get(route('company.profile.preview'))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('companies.show', $p->slug))->assertNotFound();
        $this->assertFalse($p->fresh()->is_published);
    }

    public function test_public_profile_uses_only_explicit_public_fields_and_language_fallback(): void
    {
        [$u,$w,$c] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $this->app['auth']->forgetGuards();
        foreach (['ar', 'en'] as $lang) {
            $this->get(route('companies.show', ['slug' => $p->slug, 'lang' => $lang]))->assertOk()->assertSee('Dubai Office Supply')->assertDontSee($u->email)->assertDontSee('Private Legal Company')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_incomplete_publish_and_invalid_sector_or_website_are_rejected(): void
    {
        [$u] = $this->company();
        foreach ([['about_en' => 'short'], ['sector' => 'invented'], ['website' => 'javascript:alert(1)'], ['country' => ''], ['name_en' => '']] as $extra) {
            $this->save($u, $extra)->assertSessionHasErrors();
            $this->assertDatabaseCount('company_public_profiles', 0);
        }
        $this->save($u, ['is_published' => 0, 'about_en' => ''])->assertRedirect();
        $this->assertDatabaseCount('company_public_profiles', 1);
    }

    public function test_stale_save_cannot_overwrite_and_slug_is_stable(): void
    {
        [$u] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $this->save($u, ['name_en' => 'Stale'])->assertSessionHasErrors('version');
        $this->save($u, ['version' => 1, 'name_en' => 'Updated name'])->assertRedirect();
        $this->assertSame($p->slug, $p->fresh()->slug);
        $this->assertSame(2, $p->fresh()->version);
    }

    public function test_members_unverified_users_admins_and_guests_cannot_manage_profiles(): void
    {
        [$owner,$w,$c] = $this->company();
        $member = User::factory()->create(['customer_id' => $c->id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'member']);
        $this->actingAs($member)->get(route('company.profile.edit'))->assertForbidden();
        $this->save($member)->assertForbidden();
        $owner->email_verified_at = null;
        $owner->save();
        $this->save($owner)->assertRedirect(route('verification.notice'));
        $owner->email_verified_at = now();
        $owner->save();
        $owner->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->save($owner)->assertForbidden();
        auth()->logout();
        $this->get(route('company.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_manager_can_edit_but_cannot_assign_profile_to_another_company(): void
    {
        [$owner,$w,$c] = $this->company();
        [$other,$otherW] = $this->company();
        $manager = User::factory()->create(['customer_id' => $c->id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $manager->id, 'role' => 'manager']);
        $this->save($manager, ['workspace_id' => $otherW->id])->assertSessionHasErrors('workspace_id');
        $this->save($manager, ['is_verified' => 1])->assertSessionHasErrors('is_verified');
        $this->save($manager)->assertRedirect();
        $this->assertSame($w->id, CompanyPublicProfile::first()->workspace_id);
        $this->actingAs($other)->get(route('company.profile.preview'))->assertNotFound();
    }

    public function test_suspension_and_unpublish_remove_public_profile_immediately(): void
    {
        [$u,$w,$c] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $w->update(['status' => 'suspended']);
        $this->get(route('companies.show', $p->slug))->assertNotFound();
        $this->save($u, ['version' => 1])->assertForbidden();
        $w->update(['status' => 'active']);
        $c->update(['is_active' => false]);
        $this->get(route('companies.show', $p->slug))->assertNotFound();
        $c->update(['is_active' => true]);
        $this->save($u, ['version' => 1, 'is_published' => 0])->assertRedirect();
        $this->get(route('companies.show', $p->slug))->assertNotFound();
    }

    public function test_public_listings_are_scoped_and_use_shared_eligibility(): void
    {
        [$u,$w] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        [$other,$otherW] = $this->company();
        foreach ([[$w, 'Available chairs', true], [$w, 'Closed secret', false], [$otherW, 'Other company secret', true]] as [$workspace,$title,$active]) {
            Opportunity::create(['company_workspace_id' => $workspace->id, 'customer_id' => $workspace->customer_id, 'title' => $title, 'description' => 'A business opportunity for companies.', 'stage' => 'new', 'opportunity_type' => 'sell', 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'marketplace_state' => $active ? 'published' : 'closed', 'published_at' => now()->subMinute(), 'expires_at' => now()->addDay()]);
        }
        $this->get(route('companies.show', $p->slug))->assertOk()->assertSee('Available chairs')->assertDontSee('Closed secret')->assertDontSee('Other company secret');
    }

    public function test_text_is_escaped_and_save_does_not_change_customer_or_finances(): void
    {
        [$u,$w,$c] = $this->company();
        $before = $c->fresh()->getAttributes();
        $this->save($u, ['name_en' => '<script>alert(1)</script>', 'specialties' => '<img src=x onerror=alert(1)>'])->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $this->get(route('companies.show', $p->slug))->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;img', false);
        $this->assertSame($before, $c->fresh()->getAttributes());
        $this->assertDatabaseCount('company_billing_receipts', 0);
        $this->assertDatabaseCount('invoice_payments', 0);
        $this->assertSame(1, CompanyPlatformAudit::where('action', 'company.profile_saved')->count());
    }

    public function test_public_listing_links_to_profile_and_profile_pages_are_read_only(): void
    {
        [$u,$w] = $this->company();
        $this->save($u)->assertRedirect();
        $p = CompanyPublicProfile::firstOrFail();
        $o = Opportunity::create(['company_workspace_id' => $w->id, 'customer_id' => $w->customer_id, 'title' => 'Public sourcing request', 'description' => 'Commercial products for a Dubai office.', 'stage' => 'new', 'opportunity_type' => 'buy', 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'marketplace_state' => 'published', 'published_at' => now()->subMinute(), 'expires_at' => now()->addDay()]);
        $before = $p->fresh()->getAttributes();
        $this->get(route('opportunities.show', $o->slug))->assertOk()->assertSee(route('companies.show', ['slug' => $p->slug, 'lang' => app()->getLocale()]), false);
        $this->get(route('companies.show', $p->slug))->assertOk();
        $this->assertSame($before, $p->fresh()->getAttributes());
        $this->save($u, ['version' => 1, 'is_published' => 0])->assertRedirect();
        $this->get(route('opportunities.show', $o->slug))->assertOk()->assertDontSee(route('companies.show', ['slug' => $p->slug, 'lang' => app()->getLocale()]), false);
    }

    public function test_audit_failure_rolls_back_profile(): void
    {
        [$u] = $this->company();
        Event::listen('eloquent.creating: '.CompanyPlatformAudit::class, function () {
            throw new \RuntimeException('audit unavailable');
        });
        $this->withoutExceptionHandling();
        try {
            $this->save($u);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('audit unavailable', $e->getMessage());
        }
        $this->assertDatabaseCount('company_public_profiles', 0);
    }

    public function test_both_languages_editor_and_company_entry_links_render(): void
    {
        [$u] = $this->company();
        foreach (['ar', 'en'] as $lang) {
            $this->actingAs($u)->get(route('company.profile.edit', ['lang' => $lang]))->assertOk()->assertSee('name_ar')->assertSee('name_en');
        }
        $this->get(route('company.marketplace'))->assertOk()->assertSee(route('company.profile.edit'), false);
        $this->get(route('company.workspace'))->assertOk()->assertSee(route('company.profile.edit'), false);
    }
}
