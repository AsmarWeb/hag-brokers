<?php

namespace Tests\Feature;

use App\Models\CompanyPublicProfile;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\PlatformPlan;
use App\Models\Role;
use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class MobileApiFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function payload(array $x = []): array
    {
        return array_replace(['account_type' => 'individual', 'name' => 'Person', 'email' => 'mobile@example.test', 'phone' => '+971501234567', 'password' => 'correct-password-123', 'password_confirmation' => 'correct-password-123', 'device_name' => 'phone'], $x);
    }

    private function bearer(User $u, array $abilities = ['mobile:v1']): array
    {
        auth()->forgetGuards();

        return ['Authorization' => 'Bearer '.$u->createToken('mobile:phone', $abilities, now()->addDay())->plainTextToken];
    }

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'PRIVATE LEGAL NAME', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w];
    }

    private function listing(User $u): Opportunity
    {
        app(CompanyMarketplaceService::class)->publish($u, ['title' => 'International supply', 'opportunity_type' => 'sell', 'category' => 'food', 'description' => 'Public commercial information for international buyers.', 'currency' => 'AED', 'estimated_value' => '1250.00', 'notes' => 'SECRET_NOTE', 'commission_rate' => '17.00', 'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s')], (string) Str::uuid(), []);

        return Opportunity::latest('id')->firstOrFail();
    }

    public function test_individual_registration_has_no_workspace_and_issues_scoped_expiring_token(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/auth/register', $this->payload())
            ->assertAccepted()
            ->assertJsonPath('data.email', 'mobile@example.test')
            ->assertJsonPath('data.verification_required', true);
        $u = User::firstOrFail();
        $this->assertNull($u->customer_id);
        $this->assertDatabaseCount('company_workspaces', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertFalse($u->hasVerifiedPhone());
        $this->assertTrue($u->phone_verification_required);
        $this->postJson('/api/v1/auth/login', [
            'email' => $u->email, 'password' => 'correct-password-123', 'device_name' => 'phone',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
        Notification::assertSentTo($u, MobileVerifyEmail::class);
    }

    public function test_company_registration_is_atomic_and_uses_free_workspace(): void
    {
        Notification::fake();
        $this->artisan('platform:install-plans');
        PlatformPlan::where('code', 'free')->update(['is_available' => false]);
        $d = $this->payload(['account_type' => 'company', 'company_name' => 'Global Co', 'phone' => '+971500000001', 'country_code' => 'AE']);
        $this->postJson('/api/v1/auth/register', $d)->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
        PlatformPlan::where('code', 'free')->update(['is_available' => true]);
        config(['platform_billing.free_launch' => true]);
        $this->postJson('/api/v1/auth/register', $d)->assertAccepted()->assertJsonPath('data.verification_required', true);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan(CompanyWorkspace::first())['code']);
    }

    public function test_registration_rejects_privileges_weak_password_and_duplicates(): void
    {
        Notification::fake();
        $this->postJson('/api/v1/auth/register', $this->payload(['role' => 'admin']))->assertUnprocessable();
        $this->postJson('/api/v1/auth/register', $this->payload(['password' => 'short', 'password_confirmation' => 'short']))->assertUnprocessable();
        User::factory()->create(['email' => 'mobile@example.test']);
        $this->postJson('/api/v1/auth/register', $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_rotates_named_device_and_logout_preserves_other_devices(): void
    {
        $u = User::factory()->create(['email' => 'owner@example.test', 'password' => Hash::make('correct-password-123')]);
        $u->createToken('mobile:other', ['mobile:v1']);
        $d = ['email' => $u->email, 'password' => 'correct-password-123', 'device_name' => 'phone'];
        $first = $this->postJson('/api/v1/auth/login', $d)->assertOk()->json('data.access_token');
        $second = $this->postJson('/api/v1/auth/login', $d)->assertOk()->json('data.access_token');
        $this->assertDatabaseCount('personal_access_tokens', 2);
        $this->withHeaders(['Authorization' => 'Bearer '.$second])->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNull(PersonalAccessToken::findToken($first));
    }

    public function test_guest_wrong_scope_admin_and_expired_tokens_are_rejected(): void
    {
        $this->get('/api/v1/me')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        $u = User::factory()->create();
        $this->withHeaders($this->bearer($u, ['integration:read']))->getJson('/api/v1/me')->assertForbidden();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->withHeaders($this->bearer($u))->getJson('/api/v1/me')->assertForbidden();
        $other = User::factory()->create();
        $expired = $other->createToken('mobile:expired', ['mobile:v1'], now()->subMinute())->plainTextToken;
        auth()->forgetGuards();
        $this->withHeaders(['Authorization' => 'Bearer '.$expired])->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_session_cannot_replace_bearer_token(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_device_revocation_cannot_target_another_user(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $t = $b->createToken('mobile:foreign', ['mobile:v1'])->accessToken;
        $h = $this->bearer($a);
        $this->withHeaders($h)->deleteJson('/api/v1/auth/devices/'.$t->id)->assertNotFound();
        $this->assertModelExists($t);
        $this->withHeaders($h)->getJson('/api/v1/auth/devices')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_verification_link_is_signed_expiring_and_email_bound(): void
    {
        $u = User::factory()->unverified()->create();
        $params = ['id' => $u->id, 'hash' => sha1($u->email)];
        $url = URL::temporarySignedRoute('api.v1.verify', now()->addHour(), $params);
        $this->getJson($url)->assertOk();
        $this->assertTrue($u->fresh()->hasVerifiedEmail());
        $this->getJson(str_replace('signature=', 'signature=x', $url))->assertForbidden();
        $this->getJson(URL::temporarySignedRoute('api.v1.verify', now()->subMinute(), $params))->assertForbidden();
        $u->update(['email' => 'changed@example.test']);
        $this->getJson($url)->assertForbidden();
    }

    public function test_password_reset_revokes_tokens_and_replay_fails(): void
    {
        Notification::fake();
        $u = User::factory()->create();
        $this->bearer($u);
        $token = Password::createToken($u);
        $d = ['email' => $u->email, 'token' => $token, 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];
        $this->postJson('/api/v1/auth/reset-password', $d)->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertTrue(Hash::check('new-password-123', $u->fresh()->password));
        $this->postJson('/api/v1/auth/reset-password', $d)->assertUnprocessable();
    }

    public function test_forgot_password_has_generic_response(): void
    {
        Notification::fake();
        $u = User::factory()->create();
        $a = $this->postJson('/api/v1/auth/forgot-password', ['email' => $u->email])->assertOk()->json();
        $b = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'absent@example.test'])->assertOk()->json();
        $this->assertSame($a, $b);
    }

    public function test_public_api_uses_shared_eligibility_and_explicit_fields(): void
    {
        [$u,$w] = $this->company();
        $o = $this->listing($u);
        $r = $this->getJson('/api/v1/opportunities?type=sell&per_page=1')->assertOk()->assertJsonPath('meta.total', 1);
        $this->assertSame('1250.00', $r->json('data.0.price'));
        $r = $this->getJson('/api/v1/opportunities/'.$o->slug)->assertOk();
        foreach (['SECRET_NOTE', 'commission_rate', 'submitted_by', 'customer_id', 'PRIVATE LEGAL NAME'] as $secret) {
            $r->assertDontSee($secret);
        }
        $this->getJson('/api/v1/opportunities?per_page=999')->assertUnprocessable();
        $this->getJson('/api/v1/opportunities?category=invalid')->assertUnprocessable();
        $this->getJson('/api/v1/opportunities/'.$o->id)->assertNotFound();
        $w->update(['status' => 'suspended']);
        $this->getJson('/api/v1/opportunities/'.$o->slug)->assertNotFound();
        $this->getJson('/api/v1/opportunities')->assertJsonPath('meta.total', 0);
    }

    public function test_closed_private_expired_opportunities_are_hidden(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u);
        foreach ([['marketplace_state' => 'closed'], ['visibility' => 'private'], ['expires_at' => now()->subDay()]] as $change) {
            $fresh = Opportunity::find($o->id);
            $old = $fresh->only(array_keys($change));
            $fresh->forceFill($change)->save();
            $this->getJson('/api/v1/opportunities/'.$o->slug)->assertNotFound();
            $fresh->forceFill($old)->save();
        }
    }

    public function test_public_profiles_and_languages_are_scoped(): void
    {
        [$u,$w] = $this->company();
        $p = CompanyPublicProfile::create(['workspace_id' => $w->id, 'slug' => 'company-public-test', 'name_ar' => 'شركة عالمية', 'name_en' => 'Global Co', 'is_published' => true, 'version' => 1]);
        $this->getJson('/api/v1/companies/'.$p->slug.'?lang=ar')->assertOk()->assertJsonPath('data.name', 'شركة عالمية')->assertDontSee('PRIVATE LEGAL NAME');
        $p->update(['is_published' => false]);
        $this->getJson('/api/v1/companies/'.$p->slug)->assertNotFound();
        $this->getJson('/api/v1/catalog?lang=xx')->assertUnprocessable();
        $this->getJson('/api/v1/catalog?lang=en')->assertOk()->assertHeader('X-API-Version', '1')->assertHeader('Content-Language', 'en');
    }

    public function test_login_rate_limit_and_json_without_accept_header(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'absent@example.test', 'password' => 'wrong', 'device_name' => 'phone'])->assertUnprocessable();
        }
        $this->post('/api/v1/auth/login', ['email' => 'absent@example.test', 'password' => 'wrong', 'device_name' => 'phone'])->assertStatus(429)->assertHeader('Content-Type', 'application/json');
    }

    public function test_workspace_reuses_membership_verification_and_free_plan(): void
    {
        [$u,$w] = $this->company();
        config(['platform_billing.free_launch' => true]);
        $this->withHeaders($this->bearer($u))->getJson('/api/v1/company/workspace')->assertOk()->assertJsonPath('data.plan.code', 'free');
        $u->forceFill(['email_verified_at' => null])->save();
        $this->withHeaders($this->bearer($u))->getJson('/api/v1/company/workspace')->assertForbidden();
        $outsider = User::factory()->create();
        $this->withHeaders($this->bearer($outsider))->getJson('/api/v1/company/workspace')->assertForbidden();
        $u->forceFill(['email_verified_at' => now()])->save();
        $w->update(['status' => 'suspended']);
        $this->withHeaders($this->bearer($u))->getJson('/api/v1/company/workspace')->assertForbidden();
    }
}
