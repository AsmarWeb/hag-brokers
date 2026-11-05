<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $u;
    }

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Test '.Str::random(5), 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);
        if ($role !== 'owner') {
            CompanyMember::where('user_id', $u->id)->update(['role' => $role]);
        }

return [$u, $w];
    }

    private function marine($w, string $kind = 'yacht'): Opportunity
    {
        $o = Opportunity::create(['title' => 'Marine <script>alert(1)</script>', 'description' => 'Public marine offer', 'opportunity_type' => 'sell', 'category' => 'marine', 'customer_id' => $w->customer_id, 'company_workspace_id' => $w->id, 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'published_at' => now()->subMinute(), 'expires_at' => now()->addDays(10), 'marketplace_state' => 'published', 'notes' => 'PRIVATE SOURCE NOTES']);
        $o->vessel()->create(['listing_type' => 'sale', 'vessel_category' => $kind]);

        return $o;
    }

    private function loc(array $extra = []): array
    {
        return array_replace(['version' => 0, 'is_public' => 1, 'confirm_public' => 1, 'latitude' => '25.234567', 'longitude' => '55.345678', 'label' => 'Dubai <script>x</script>'], $extra);
    }

    private function settings(array $extra = []): array
    {
        config(['app.url' => 'https://hagbrokers.net', 'seo.canonical_url' => 'https://hagbrokers.net']);

        return array_replace(['version' => 0, 'download_enabled' => 1, 'map_enabled' => 1, 'android_url' => 'https://hagbrokers.net/downloads/hag-1.0.apk', 'android_version' => '1.0.0', 'released_on' => now()->toDateString()], $extra);
    }

    public function test_disabled_default_and_bilingual_download_and_real_home(): void
    {
        $this->get('/download')->assertNotFound();
        $this->actingAs($this->admin())->put('/admin/public-experience', $this->settings())->assertRedirect();
        foreach (['ar', 'en'] as $lang) {
            $this->get('/download?lang='.$lang)->assertOk()->assertSee('hag-1.0.apk')->assertSee('1.0.0');
            $this->get('/?lang='.$lang)->assertOk()->assertSee('android-app')->assertDontSee('HAG DEMO')->assertDontSee('Gulf Network Active');
            $this->get('/admin/public-experience?lang='.$lang)->assertOk();
        }
        $this->put('/admin/public-experience', $this->settings(['version' => 1, 'download_enabled' => 0]))->assertRedirect();
        $this->get('/download')->assertNotFound();
        $this->get('/')->assertDontSee('id="android-app"', false);
    }

    public function test_settings_permissions_url_validation_stale_and_audit(): void
    {
        $this->putJson('/admin/public-experience', $this->settings())->assertUnauthorized();
        [$u] = $this->company();
        $this->actingAs($u)->putJson('/admin/public-experience', $this->settings())->assertForbidden();
        $a = $this->admin();
        $a->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($a)->get('/admin/public-experience')->assertForbidden();
        $a->markEmailAsVerified();
        $this->actingAs($a);
        foreach (['https://evil.test/x.apk', 'http://hagbrokers.net/downloads/a.apk', 'https://hagbrokers.net@evil.test/a.apk', 'https://hagbrokers.net/downloads/../x.apk', 'https://hagbrokers.net/downloads/x.apk?secret=x', 'javascript:alert(1)', 'https://expo.dev/accounts/other/projects/wrong/builds/'.Str::uuid()] as $url) {
            $this->putJson('/admin/public-experience', $this->settings(['android_url' => $url]))->assertUnprocessable();
        }
        $this->putJson('/admin/public-experience', $this->settings(['android_url' => null]))->assertUnprocessable();
        $this->assertDatabaseCount('public_experience_settings', 0);
        $url = 'https://expo.dev/accounts/asmarweb/projects/hag-brokers-mobile/builds/'.Str::uuid();
        $this->put('/admin/public-experience', $this->settings(['android_url' => $url]))->assertRedirect();
        $this->put('/admin/public-experience', $this->settings())->assertStatus(409);
        $this->assertDatabaseCount('public_experience_audits', 1);
    }

    public function test_map_readonly_eligibility_precision_and_escaped_content(): void
    {
        [$u,$w] = $this->company();
        $o = $this->marine($w);
        $this->getJson('/api/v1/maritime-map')->assertOk()->assertJsonCount(0, 'data.listings')->assertJsonPath('data.live_tracking', false);
        $this->actingAs($u)->put('/company/map-locations/'.$o->id, $this->loc())->assertRedirect();
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(1, 'data.listings')->assertJsonPath('data.listings.0.latitude', 25.23)->assertJsonPath('data.listings.0.longitude', 55.35)->assertJsonMissingPath('data.listings.0.notes')->assertDontSee('PRIVATE SOURCE NOTES');
        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
        $this->assertDatabaseCount('public_experience_audits', 1);
        foreach ([['visibility' => 'private'], ['visibility' => 'public', 'expires_at' => now()->subSecond()], ['expires_at' => now()->addDay(), 'marketplace_state' => 'closed']] as $change) {
            $o->update($change);
            $this->getJson('/api/v1/maritime-map')->assertJsonCount(0, 'data.listings');
        }
        $o->update(['marketplace_state' => 'published']);
        $o->moderation()->create(['hidden' => true]);
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(0, 'data.listings');
        $o->moderation()->update(['hidden' => false]);
        $w->update(['status' => 'suspended']);
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(0, 'data.listings');
    }

    public function test_location_scope_consent_validation_stale_and_removal(): void
    {
        [$u,$w] = $this->company();
        $o = $this->marine($w);
        $path = '/company/map-locations/'.$o->id;
        $this->get($path)->assertRedirect();
        [$other] = $this->company();
        $this->actingAs($other)->put($path, $this->loc())->assertNotFound();
        [$member] = $this->company('member');
        $this->actingAs($member)->put($path, $this->loc())->assertForbidden();
        $this->actingAs($u)->get($path)->assertOk();
        foreach ([['confirm_public' => 0], ['latitude' => 91], ['longitude' => -181], ['label' => ''], ['latitude' => null]] as $bad) {
            $this->putJson($path, $this->loc($bad))->assertUnprocessable();
        }$this->assertDatabaseCount('opportunity_map_locations', 0);
        $this->put($path, $this->loc())->assertRedirect();
        $this->put($path, $this->loc(['label' => 'stale']))->assertStatus(409);
        $this->put($path, $this->loc(['version' => 1, 'is_public' => 0, 'confirm_public' => 0]))->assertRedirect();
        $this->assertDatabaseHas('opportunity_map_locations', ['opportunity_id' => $o->id, 'latitude' => null, 'is_public' => false]);
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(0, 'data.listings');
    }

    public function test_admin_location_financial_isolation_and_audit_rollback(): void
    {
        [$u,$w] = $this->company();
        $o = $this->marine($w, 'commercial_vessel');
        $before = $o->fresh()->getRawOriginal();
        $counts = [];
        foreach (['company_workspaces', 'company_billing_receipts', 'issued_invoices', 'quotations'] as $t) {
            $counts[$t] = DB::table($t)->count();
        }
        $path = '/admin/public-experience/opportunities/'.$o->id.'/location';
        $this->actingAs($this->admin())->get($path)->assertOk();
        DB::unprepared("CREATE TRIGGER reject_public_audit BEFORE INSERT ON public_experience_audits BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        $this->put($path, $this->loc())->assertStatus(500);
        $this->assertDatabaseCount('opportunity_map_locations', 0);
        DB::unprepared('DROP TRIGGER reject_public_audit');
        $this->put($path, $this->loc())->assertRedirect();
        $this->assertSame($before, $o->fresh()->getRawOriginal());
        foreach ($counts as $t => $n) {
            $this->assertDatabaseCount($t, $n);
        }
    }

    public function test_all_asset_types_and_global_map_switch(): void
    {
        [$u,$w] = $this->company();
        foreach (['boat', 'yacht', 'commercial_vessel'] as $kind) {
            $o = $this->marine($w, $kind);
            $this->actingAs($u)->put('/company/map-locations/'.$o->id, $this->loc())->assertRedirect();
        }
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(3, 'data.listings')->assertHeader('X-API-Version', '1');
        $o->vessel()->update(['listing_type' => 'wanted']);
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(2, 'data.listings');
        $this->actingAs($this->admin())->put('/admin/public-experience', $this->settings(['download_enabled' => 0, 'android_url' => null, 'map_enabled' => 0]))->assertRedirect();
        $this->getJson('/api/v1/maritime-map')->assertJsonCount(0,'data.listings');
        $this->get('/')->assertOk()->assertDontSee('id="gulf-map"',false);
    }
}
